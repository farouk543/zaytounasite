<?php

use App\Filament\Resources\LiveSubscriptions\Pages\ViewLiveSubscription;
use App\Models\MarketingGoal;
use App\Models\Order;
use App\Models\PageView;
use App\Models\Testimonial;
use App\Models\User;
use App\Notifications\MonthlyMarketingReport;
use App\Notifications\TestimonialReceived;
use App\Services\Analytics\Funnel;
use App\Support\AdminNotifier;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

const BRIDGE_TOKEN = 'test-token-0123456789abcdef';

beforeEach(function () {
    config(['zaytouna.platform_url' => 'https://plateforme.test', 'zaytouna.platform_stats_token' => BRIDGE_TOKEN]);
    Role::findOrCreate('admin', 'web');
    $this->admin = User::factory()->create(['email' => 'boss@zaytouna.test']);
    $this->admin->assignRole('admin');
    $this->admin->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');
});

function regimeOrder(array $attributes = []): Order
{
    return Order::create($attributes + [
        'user_id' => User::factory()->create()->id, 'type' => 'regime', 'regime' => 'qatar', 'pack' => 'individual',
        'status' => 'pending_manual', 'total_cents' => 45000, 'currency' => 'QAR',
        'parent_name' => 'Mme Secret', 'student_name' => 'Youssef Ben Ali', 'contact_email' => 'maman@example.com',
    ]);
}

it('relays admin alerts to the platform without personal data', function () {
    Http::fake(['plateforme.test/*' => Http::response(['notified' => 1])]);
    $this->withoutDefer();

    $testimonial = Testimonial::create(['author_name' => 'Samia Secret', 'rating' => 5, 'quote' => 'Très bien', 'source' => 'site']);
    AdminNotifier::send(new TestimonialReceived($testimonial));

    Http::assertSent(fn (Request $r) => $r->url() === 'https://plateforme.test/api/site/events'
        && $r->hasHeader('Authorization', 'Bearer '.BRIDGE_TOKEN)
        && $r['type'] === 'testimonial'
        && $r['title'] === 'Nouvel avis de parent (5/5) à valider'
        && ! str_contains(json_encode($r->data()), 'Secret'));
});

it('does not call the platform when the bridge is not configured', function () {
    config(['zaytouna.platform_stats_token' => null]);
    Http::fake();
    $this->withoutDefer();

    AdminNotifier::send(new TestimonialReceived(Testimonial::create(['author_name' => 'A', 'rating' => 5, 'quote' => 'x'])));

    Http::assertNothingSent();
});

it('creates the student account on the platform and pre-fills the access form', function () {
    Http::fake(['plateforme.test/api/site/students' => Http::response(['created' => true, 'login' => 'maman@example.com', 'password' => 'Provisoire123'], 201)]);
    $order = regimeOrder();

    $this->actingAs($this->admin);
    Livewire::test(ViewLiveSubscription::class, ['record' => $order->id])
        ->callAction('createPlatformAccount', data: ['name' => 'Youssef Ben Ali', 'email' => 'maman@example.com', 'timezone' => 'Asia/Qatar'])
        ->assertHasNoActionErrors()
        ->mountAction('approve')
        ->assertActionDataSet(['login' => 'maman@example.com', 'password' => 'Provisoire123']);

    expect($order->fresh()->platform_username)->toBe('maman@example.com');
    Http::assertSent(fn (Request $r) => $r['site_order_id'] === $order->id && $r['timezone'] === 'Asia/Qatar' && $r['name'] === 'Youssef Ben Ali');
});

it('reports a platform refusal without changing the order', function () {
    Http::fake(['plateforme.test/*' => Http::response(['created' => false, 'reason' => 'email_taken'], 409)]);
    $order = regimeOrder();

    $this->actingAs($this->admin);
    Livewire::test(ViewLiveSubscription::class, ['record' => $order->id])
        ->callAction('createPlatformAccount', data: ['name' => 'Youssef', 'email' => 'prof@example.com', 'timezone' => 'Asia/Qatar'])
        ->assertNotified('Compte non créé');

    expect($order->fresh()->platform_username)->toBeNull();
});

it('imports public platform reviews as unpublished testimonials, once', function () {
    Http::fake(['plateforme.test/api/site/testimonials' => Http::response(['data' => [
        ['id' => 9, 'rating' => 5, 'comment' => 'Le professeur explique très bien, merci !', 'author' => 'Yasmine B.', 'subject' => 'Mathématiques', 'country' => 'FR', 'date' => '2026-10-20'],
    ]])]);

    $this->artisan('testimonials:import-platform')->assertSuccessful();
    $this->artisan('testimonials:import-platform')->assertSuccessful();

    expect(Testimonial::count())->toBe(1);
    $t = Testimonial::first();
    expect($t->is_published)->toBeFalse()
        ->and($t->source)->toBe('plateforme')
        ->and($t->author_name)->toBe('Yasmine B.')
        ->and($t->author_role)->toBe('Élève — Mathématiques')
        ->and($t->country)->toBe('FR');
});

it('builds the full funnel from site data and platform stats', function () {
    $this->travelTo('2026-11-20 12:00:00');
    Http::fake(['plateforme.test/api/marketing/stats*' => Http::response([
        'retained_students' => 4,
        'from_site' => ['accounts_total' => 3, 'accounts_new' => 2, 'active' => 1],
        'by_country' => ['QA' => ['site_accounts_new' => 1, 'site_students_active' => 1, 'retained_students' => 2]],
    ])]);

    $parent = User::factory()->create();
    foreach (['a', 'b', 'c', 'd'] as $h) {
        PageView::create(['visitor_hash' => str_repeat($h, 64), 'path' => '/', 'country' => 'QA', 'created_at' => '2026-11-10 10:00:00',
            'user_id' => $h === 'a' ? $parent->id : null]);
    }
    regimeOrder(['user_id' => $parent->id, 'status' => 'paid']);
    regimeOrder(['user_id' => $parent->id]);

    $funnel = app(Funnel::class)->month('2026-11');
    $qa = collect($funnel['rows'])->firstWhere('code', 'QA');

    expect($qa)->toMatchArray(['visitors' => 4, 'orders' => 2, 'paid' => 1, 'accounts' => 1, 'active' => 1, 'retained' => 2])
        ->and($qa['rates']['orders'])->toBe(50)
        ->and($funnel['total']['accounts'])->toBe(2)
        ->and($funnel['total']['active'])->toBe(1);
});

it('emails the monthly report to the admins', function () {
    Notification::fake();
    Http::fake(['plateforme.test/*' => Http::response([], 500)]);
    config(['zaytouna.admin_emails' => ['equipe@zaytouna.test']]);
    $this->travelTo('2026-12-01 08:00:00');
    MarketingGoal::create(['month' => '2026-11', 'metric' => 'visitors', 'country' => null, 'target' => 10]);
    PageView::create(['visitor_hash' => str_repeat('z', 64), 'path' => '/', 'country' => 'TN', 'created_at' => '2026-11-10 10:00:00']);

    $this->artisan('marketing:monthly-report')->assertSuccessful();

    Notification::assertSentOnDemandTimes(MonthlyMarketingReport::class, 2);
    Notification::assertSentOnDemand(MonthlyMarketingReport::class, function ($notification, $channels, $notifiable) {
        $html = (string) $notification->toMail($notifiable)->render();

        return $notification->report['month'] === '2026-11'
            && str_contains($html, 'Novembre 2026')
            && str_contains($html, 'Parcours complet')
            && str_contains($html, 'Visiteurs uniques');
    });
});

it('shows the funnel on the statistics page and in the site API', function () {
    Http::fake(['plateforme.test/*' => Http::response([], 500)]);

    $this->actingAs($this->admin)->get('/admin/statistiques')->assertOk()->assertSee('Parcours complet');

    $this->withToken(BRIDGE_TOKEN)->getJson('/api/site/stats')->assertOk()
        ->assertJsonPath('funnel.platform', false)
        ->assertJsonStructure(['funnel' => ['rows', 'total' => ['visitors', 'orders', 'paid', 'rates'], 'leak']]);
});
