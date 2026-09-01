<?php

use App\Models\Course;
use App\Models\Exercise;
use App\Models\Level;
use App\Models\Order;
use App\Models\Subject;
use App\Models\Track;
use App\Models\User;
use App\Services\PriceResolver;

function makeCourse(array $attributes = []): Course
{
    $track = Track::create(['slug' => 'trk-'.uniqid(), 'name' => 'Track']);
    $level = Level::create(['track_id' => $track->id, 'slug' => 'lvl-'.uniqid(), 'name' => 'Level']);
    $subject = Subject::create(['level_id' => $level->id, 'slug' => 'subj-'.uniqid(), 'name' => 'Subject']);

    return Course::create(array_merge([
        'subject_id' => $subject->id,
        'slug' => 'course-'.uniqid(),
        'title' => 'Course',
        'is_paid' => true,
        'price_cents' => 20000, // 200 TND
        'currency' => 'TND',
        'is_published' => true,
    ], $attributes));
}

it('uses an exact per-market price when one is defined', function () {
    $exercise = Exercise::create([
        'title' => 'Ex', 'is_paid' => true, 'price_cents' => 10000, 'currency' => 'TND', 'is_published' => true,
    ]);
    $exercise->prices()->create(['currency' => 'EUR', 'price_cents' => 9900]);

    $quote = app(PriceResolver::class)->resolve($exercise->fresh('prices'), 'EUR');

    expect($quote->priceCents)->toBe(9900)
        ->and($quote->currency)->toBe('EUR')
        ->and($quote->isEstimated)->toBeFalse();
});

it('falls back to a rounded conversion when no market price exists', function () {
    $course = makeCourse(['price_cents' => 20000, 'currency' => 'TND']);

    $quote = app(PriceResolver::class)->resolve($course, 'EUR');

    expect($quote->isEstimated)->toBeTrue()
        ->and($quote->currency)->toBe('EUR')
        ->and($quote->priceCents % 100)->toBe(0); // rounded to whole EUR
});

it('does not convert when the target currency is the base currency', function () {
    $course = makeCourse(['price_cents' => 20000, 'currency' => 'TND']);

    $quote = app(PriceResolver::class)->resolve($course, 'TND');

    expect($quote->priceCents)->toBe(20000)
        ->and($quote->isEstimated)->toBeFalse();
});

it('lets the visitor pick a currency and keeps it over geo-detection', function () {
    $this->get('/devise/EUR')->assertRedirect();

    expect(session('app_currency'))->toBe('EUR')
        ->and(session('app_currency_manual'))->toBeTrue();

    // A later request with a Cloudflare country header must not override the manual choice.
    $this->withServerVariables(['HTTP_CF_IPCOUNTRY' => 'SA'])->get('/');

    expect(session('app_currency'))->toBe('EUR');
});

it('rejects an unsupported currency code', function () {
    $this->get('/devise/JPY')->assertStatus(400);
});

it('shows regime packs in the regime currency when there is no geo signal', function () {
    Track::create(['slug' => 'qatar', 'name' => 'Qatar', 'is_active' => true]);

    $this->get('/regimes/qatar')
        ->assertOk()
        ->assertSee('QAR', false)
        ->assertDontSee('149 €', false);
});

it('shows regime packs in the visitor currency when geolocation resolves it', function () {
    Track::create(['slug' => 'qatar', 'name' => 'Qatar', 'is_active' => true]);

    $this->withServerVariables(['HTTP_CF_IPCOUNTRY' => 'FR'])
        ->get('/regimes/qatar')
        ->assertOk()
        ->assertSee('149 € / mois', false);
});

it('serves the France regime in EUR by default', function () {
    Track::create(['slug' => 'france', 'name' => 'France', 'is_active' => true]);

    $this->get('/lang/fr');

    $this->get('/regimes/france')
        ->assertOk()
        ->assertSee('Programme français', false)
        ->assertSee('regimes/france/start', false)
        ->assertSee('€', false)
        ->assertDontSee(' DT ', false);
});

it('shows France regime packs in the visitor currency when detected', function () {
    Track::create(['slug' => 'france', 'name' => 'France', 'is_active' => true]);

    $this->withServerVariables(['HTTP_CF_IPCOUNTRY' => 'SA'])
        ->get('/regimes/france')
        ->assertOk()
        ->assertSee('SAR', false);
});

it('adapts Club d\'ete pack prices to the visitor currency', function () {
    $this->withSession(['app_currency' => 'EUR', 'app_currency_manual' => true])
        ->get('/club-ete')
        ->assertOk()
        ->assertSee('€', false)
        ->assertDontSee('40 DT', false);

    // Same PPP rule as regime packs: 40 TND essential pack scaled for EUR.
    $expected = \App\Services\CurrencyService::marketAdaptedPrice(40, 'EUR');
    expect($expected)->toBe(
        App\Models\SummerClubSubscriptionRequest::priceFor('essential', 'EUR')
    )->and($expected)->toBeGreaterThan(0);
});

it('locks the shown currency and price onto a Club d\'ete subscription request', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['app_currency' => 'EUR', 'app_currency_manual' => true])
        ->post('/club-ete/subscription-request', [
            'pack_key' => 'essential',
            'parent_name' => 'Parent',
            'student_name' => 'Enfant',
            'phone' => '+21620000000',
            'selected_subjects' => ['Français'],
        ])
        ->assertRedirect();

    $req = App\Models\SummerClubSubscriptionRequest::latest('id')->firstOrFail();

    expect($req->currency)->toBe('EUR')
        ->and((int) round($req->price))->toBe(\App\Services\CurrencyService::marketAdaptedPrice(40, 'EUR'))
        ->and((int) round($req->base_price))->toBe(40);
});

it('locks the resolved price and currency onto the order at manual checkout', function () {
    $user = User::factory()->create();
    $course = makeCourse(['price_cents' => 20000, 'currency' => 'TND']);
    $course->prices()->create(['currency' => 'EUR', 'price_cents' => 15000]); // 150 €

    $this->actingAs($user)
        ->withSession([
            'cart.items' => [(string) $course->id => 1],
            'app_currency' => 'EUR',
            'app_currency_manual' => true,
        ])
        ->post('/checkout/manual')
        ->assertRedirect(route('dashboard'));

    $order = Order::where('user_id', $user->id)->latest('id')->firstOrFail();

    expect($order->currency)->toBe('EUR')
        ->and($order->total_cents)->toBe(15000);

    $item = $order->items()->firstOrFail();

    expect($item->unit_price_cents)->toBe(15000)
        ->and($item->currency)->toBe('EUR')
        ->and($item->base_price_cents)->toBe(20000)
        ->and($item->base_currency)->toBe('TND');
});
