<?php

use App\Filament\Resources\LiveSubscriptions\Pages\ViewLiveSubscription;
use App\Models\Level;
use App\Models\Order;
use App\Models\Subject;
use App\Models\SummerClubSubscriptionRequest;
use App\Models\Track;
use App\Models\User;
use App\Notifications\NewUserRegistered;
use App\Notifications\RegimeOrderPending;
use App\Notifications\RegimeSubscriptionApproved;
use App\Notifications\SummerClubSubscriptionPending;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
    Role::findOrCreate('student', 'web');

    $this->admin = User::factory()->create(['email' => 'boss@zaytouna.test']);
    $this->admin->assignRole('admin');

    config(['zaytouna.admin_emails' => ['equipe@zaytouna.test']]);
});

function regimeSelection(): array
{
    $track = Track::create(['slug' => 'tunisia', 'name' => 'Tunisie', 'is_active' => true]);
    $level = Level::create(['track_id' => $track->id, 'slug' => '3eme', 'name' => '3ème année', 'is_active' => true]);
    $subject = Subject::create(['level_id' => $level->id, 'slug' => 'maths', 'name' => 'Mathématiques', 'is_active' => true]);

    return [
        'track_id' => $track->id,
        'level_id' => $level->id,
        'branch_id' => null,
        'grade' => null,
        'subject_id' => $subject->id,
        'pack' => 'individual',
    ];
}

it('emails the admins when someone registers', function () {
    Notification::fake();

    $this->post('/register', [
        'name' => 'Parent Test',
        'email' => 'parent@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    Notification::assertSentTo($this->admin, NewUserRegistered::class);
    Notification::assertSentOnDemand(
        NewUserRegistered::class,
        fn ($n, $channels, $notifiable) => in_array($notifiable->routes['mail'], ['boss@zaytouna.test', 'equipe@zaytouna.test'], true)
    );
    Notification::assertSentOnDemandTimes(NewUserRegistered::class, 2);
});

it('stores who subscribes to which pack and emails the admins', function () {
    Notification::fake();

    $parent = User::factory()->create();
    $sel = regimeSelection();

    $this->actingAs($parent)
        ->withSession(['regime.selection' => $sel])
        ->post(route('regimes.tunisia.submit'), [
            'parent_name' => 'Mme Ben Ali',
            'student_name' => 'Youssef',
            'phone' => '+216 22 000 000',
            'contact_email' => 'maman@example.com',
            'customer_notes' => 'Mercredi après-midi',
        ])
        ->assertRedirect(route('dashboard'));

    $order = Order::sole();

    expect($order->type)->toBe('regime')
        ->and($order->regime)->toBe('tunisia')
        ->and($order->pack)->toBe('individual')
        ->and($order->student_name)->toBe('Youssef')
        ->and($order->phone)->toBe('+216 22 000 000')
        ->and($order->total_cents)->toBeGreaterThan(0)
        ->and(\App\Support\RegimeSubscription::detailLines($order))->toBe(['3ème année — Mathématiques']);

    Notification::assertSentTo($this->admin, RegimeOrderPending::class);
    Notification::assertSentOnDemandTimes(RegimeOrderPending::class, 2);
});

it('requires the parent contact details', function () {
    $parent = User::factory()->create();

    $this->actingAs($parent)
        ->withSession(['regime.selection' => regimeSelection()])
        ->post(route('regimes.tunisia.submit'), [])
        ->assertSessionHasErrors(['parent_name', 'student_name', 'phone', 'contact_email']);

    expect(Order::count())->toBe(0);
});

it('sends the platform access to the parent when an admin accepts', function () {
    Notification::fake();

    $parent = User::factory()->create();
    $sel = regimeSelection();
    $order = Order::create([
        'user_id' => $parent->id, 'type' => 'regime', 'regime' => 'tunisia', 'pack' => 'individual',
        'selection' => $sel, 'status' => 'pending_manual', 'total_cents' => 15000, 'currency' => 'TND',
        'parent_name' => 'Mme Ben Ali', 'student_name' => 'Youssef', 'phone' => '+216', 'contact_email' => 'maman@example.com',
    ]);

    $this->actingAs($this->admin);

    Livewire::test(ViewLiveSubscription::class, ['record' => $order->id])
        ->callAction('approve', data: [
            'platform_url' => 'https://app.zaytouna.test',
            'login' => 'youssef.benali',
            'password' => 'Temp-1234',
            'start_date' => '2026-10-15',
            'schedule' => 'Mercredi 15h',
            'recipient' => 'maman@example.com',
        ])
        ->assertHasNoActionErrors();

    $order->refresh();
    expect($order->status)->toBe('paid')
        ->and($order->platform_username)->toBe('youssef.benali')
        ->and($order->access_sent_at)->not->toBeNull();

    Notification::assertSentOnDemand(
        RegimeSubscriptionApproved::class,
        function ($notification, $channels, $notifiable) {
            $html = (string) $notification->toMail($notifiable)->render();

            return $notifiable->routes['mail'] === 'maman@example.com'
                && str_contains($html, 'youssef.benali')
                && str_contains($html, 'Temp-1234')
                && str_contains($html, 'https://app.zaytouna.test')
                && str_contains($html, 'Mathématiques');
        }
    );
});

it('emails the admins about a new summer club request', function () {
    Notification::fake();

    $this->post(route('club-ete.subscription-request.store'), [
        'pack_key' => 'complete',
        'parent_name' => 'M. Trabelsi',
        'student_name' => 'Amine',
        'phone' => '+216 55 000 000',
        'email' => 'papa@example.com',
    ])->assertSessionHasNoErrors();

    expect(SummerClubSubscriptionRequest::count())->toBe(1);
    Notification::assertSentTo($this->admin, SummerClubSubscriptionPending::class);
    Notification::assertSentOnDemandTimes(SummerClubSubscriptionPending::class, 2);
});
