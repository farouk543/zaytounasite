<?php

use App\Models\ClickEvent;
use App\Models\MarketingGoal;
use App\Models\Testimonial;
use App\Models\Track;
use App\Models\User;
use App\Notifications\TestimonialReceived;
use App\Services\Analytics\GoalTracker;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

// ───────── Devise CAD ─────────

it('bills Canadian visitors in CAD with market-adapted prices', function () {
    expect(CurrencyService::isSupported('CAD'))->toBeTrue()
        ->and(CurrencyService::getCurrencyForCountry('CA'))->toBe('CAD')
        ->and(CurrencyService::SYMBOLS['CAD'])->toBe('CA$');

    // Cohérent avec le prix USD (~1,38 CAD pour 1 USD) et le prix en euros.
    $cad = CurrencyService::packPrice('individual', 'CAD');
    $usd = CurrencyService::packPrice('individual', 'USD');
    expect($cad)->toBeGreaterThan($usd)->and($cad)->toBeLessThan((int) round($usd * 1.5));
});

it('lets a visitor switch to CAD and see it on a regime page', function () {
    $this->get(route('currency.switch', 'CAD'))->assertRedirect();
    expect(session('app_currency'))->toBe('CAD');
    Track::create(['slug' => 'france', 'name' => 'France', 'is_active' => true]);

    $this->get(route('regimes.france.show'))->assertOk()->assertSee('CA$', false);
});

// ───────── Bouton WhatsApp ─────────

it('shows a tracked WhatsApp button with a localized prefilled message', function () {
    config(['zaytouna.whatsapp_number' => '21657029460']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('class="za-wa"', false)
        ->assertSee('https://wa.me/21657029460?text=', false)
        ->assertSee('data-track="WhatsApp (bouton flottant)"', false);
});

it('counts WhatsApp contacts as a goal metric but ignores share links', function () {
    $month = now(config('marketing.timezone'))->format('Y-m');
    $click = fn (string $hash, string $target) => ClickEvent::create([
        'visitor_hash' => $hash, 'page_path' => '/', 'label' => 'WhatsApp', 'target' => $target, 'kind' => 'cta',
    ]);
    $click('a', 'wa.me/21657029460');
    $click('a', 'wa.me/21657029460'); // même visiteur
    $click('b', 'wa.me/21657029460');
    $click('c', 'wa.me/'); // « partager sur WhatsApp » d'un article

    expect(app(GoalTracker::class)->actual('whatsapp', $month))->toBe(2.0)
        ->and(MarketingGoal::METRICS)->toHaveKey('whatsapp');
});

// ───────── Témoignages ─────────

it('hides the testimonials block until a review is published', function () {
    Track::create(['slug' => 'tunisia', 'name' => 'Tunisie', 'is_active' => true]);
    Testimonial::create(['author_name' => 'Samia B.', 'program' => 'tunisia', 'rating' => 5,
        'quote' => 'Avis pas encore validé par l’équipe.', 'is_published' => false]);

    $this->get(route('regimes.tunisia.show'))->assertOk()->assertDontSee('zt-card', false);
});

it('shows published reviews and AggregateRating markup from three reviews', function () {
    Track::create(['slug' => 'tunisia', 'name' => 'Tunisie', 'is_active' => true]);
    foreach ([5, 5, 4] as $i => $rating) {
        Testimonial::create(['author_name' => "Parent $i", 'program' => 'tunisia', 'country' => 'TN', 'rating' => $rating,
            'quote' => "Très bonne expérience numéro $i avec les enseignants.", 'is_published' => true]);
    }
    Testimonial::create(['author_name' => 'Parent Qatar', 'program' => 'qatar', 'rating' => 5,
        'quote' => 'Avis sur le programme qatari uniquement.', 'is_published' => true]);

    $html = $this->get(route('regimes.tunisia.show'))->assertOk()->getContent();

    expect($html)->toContain('Parent 0')->toContain('zt-card')
        ->not->toContain('Parent Qatar')
        ->toContain('"@type":"AggregateRating"')
        ->toContain('"ratingValue":4.7')
        ->toContain('"reviewCount":3');
});

it('stores a review from the public form as unpublished and notifies admins', function () {
    Notification::fake();
    Role::findOrCreate('admin');
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $admin->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP'); // double authentification configurée

    $this->get(route('reviews.create', ['programme' => 'quran']))->assertOk()->assertSee('name="rating"', false);

    $this->post(route('reviews.store'), [
        'author_name' => 'Karim T.',
        'author_role' => 'Papa de Adam',
        'country' => 'CA',
        'program' => 'quran',
        'rating' => 5,
        'quote' => 'Mon fils progresse beaucoup en tajwid, merci à toute l’équipe !',
        'consent' => '1',
    ])->assertRedirect(route('reviews.create'));

    $t = Testimonial::sole();
    expect($t->is_published)->toBeFalse()->and($t->source)->toBe('site')->and($t->country)->toBe('CA');
    Notification::assertSentTo($admin, TestimonialReceived::class);
});

it('rejects reviews without consent and silently drops bots', function () {
    $payload = ['author_name' => 'Bot', 'program' => 'general', 'rating' => 5,
        'quote' => 'Un texte suffisamment long pour passer.'];

    $this->post(route('reviews.store'), $payload)->assertSessionHasErrors('consent');
    $this->post(route('reviews.store'), $payload + ['consent' => '1', 'website' => 'http://spam.example'])
        ->assertRedirect(route('reviews.create'));

    expect(Testimonial::count())->toBe(0);
});

it('lets admins manage testimonials in the back-office', function () {
    Role::findOrCreate('admin');
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $admin->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP'); // double authentification configurée
    Testimonial::create(['author_name' => 'Leïla M.', 'program' => 'france', 'rating' => 4,
        'quote' => 'Avis reçu sur WhatsApp.', 'source' => 'site']);

    $this->actingAs($admin)->get('/admin/temoignages')->assertOk()->assertSee('Leïla M.');
});

it('lets admins use the back-office without two-factor authentication', function () {
    Role::findOrCreate('admin');
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)->get('/admin/temoignages')->assertOk();
});
