<?php

use App\Models\MarketingGoal;
use App\Models\Order;
use App\Models\PageView;
use App\Models\User;

const SITE_STATS_TOKEN = 'test-token-0123456789abcdef';

beforeEach(function () {
    config(['zaytouna.platform_stats_token' => SITE_STATS_TOKEN, 'zaytouna.platform_url' => null]);
    $this->travelTo('2026-11-16 12:00:00');
});

it('is closed without a token and rejects wrong tokens', function () {
    $this->getJson('/api/site/stats')->assertUnauthorized();
    $this->withToken('mauvais')->getJson('/api/site/stats')->assertUnauthorized();

    config(['zaytouna.platform_stats_token' => '']);
    $this->withToken('')->getJson('/api/site/stats')->assertNotFound();
});

it('validates period and month', function () {
    $this->withToken(SITE_STATS_TOKEN)->getJson('/api/site/stats?period=forever')->assertStatus(422);
    $this->withToken(SITE_STATS_TOKEN)->getJson('/api/site/stats?month=2026-13')->assertStatus(422);
});

it('returns aggregated site stats without personal data', function () {
    $user = User::factory()->create(['name' => 'Parent Secret', 'email' => 'parent@example.com']);
    PageView::create(['visitor_hash' => str_repeat('a', 64), 'path' => '/regimes/qatar', 'title' => 'Régime Qatar',
        'source' => 'Google', 'country' => 'QA', 'device' => 'mobile', 'created_at' => '2026-11-15 10:00:00', 'user_id' => $user->id]);
    PageView::create(['visitor_hash' => str_repeat('b', 64), 'path' => '/', 'title' => 'Accueil',
        'source' => 'Direct', 'country' => 'TN', 'device' => 'desktop', 'created_at' => '2026-11-15 11:00:00']);
    Order::create(['user_id' => $user->id, 'status' => 'paid', 'type' => 'regime', 'regime' => 'qatar',
        'subtotal_cents' => 45000, 'discount_cents' => 0, 'total_cents' => 45000, 'currency' => 'QAR']);
    MarketingGoal::create(['month' => '2026-11', 'metric' => 'visitors', 'country' => 'QA', 'target' => 2]);

    $response = $this->withToken(SITE_STATS_TOKEN)->getJson('/api/site/stats?period=30d&month=2026-11')->assertOk();

    $response->assertJsonPath('kpis.visitors.value', 2)
        ->assertJsonPath('sales.paid_by_currency.0.currency', 'QAR')
        ->assertJsonPath('sales.paid_by_currency.0.total', 450)
        ->assertJsonPath('sales.by_type.qatar', 1)
        ->assertJsonPath('top_pages.0.path', '/regimes/qatar')
        ->assertJsonPath('goals.rows.0.scope', 'Qatar')
        ->assertJsonPath('goals.rows.0.actual', 1);

    expect(collect($response->json('markets.rows'))->firstWhere('code', 'QA')['visitors'])->toBe(1);

    $body = $response->getContent();
    expect($body)->not->toContain('Parent Secret')
        ->not->toContain('parent@example.com')
        ->not->toContain(str_repeat('a', 64));
});
