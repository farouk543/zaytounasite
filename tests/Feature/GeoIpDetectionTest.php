<?php

use App\Services\GeoIpResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.geoip.enabled', true);
    config()->set('services.geoip.endpoint', 'https://ipwho.is/{ip}');
    config()->set('services.geoip.country_path', 'country_code');
    Cache::flush();
});

it('resolves a country from the IP provider and caches it', function () {
    Http::fake(['ipwho.is/*' => Http::response(['success' => true, 'country_code' => 'fr'])]);

    $country = app(GeoIpResolver::class)->countryFor('8.8.8.8');

    expect($country)->toBe('FR')
        ->and(Cache::get('geoip:country:8.8.8.8'))->toBe('FR');

    // Second call is served from cache, no extra HTTP request.
    app(GeoIpResolver::class)->countryFor('8.8.8.8');
    Http::assertSentCount(1);
});

it('never calls the provider for a private or invalid IP', function () {
    Http::fake();

    expect(app(GeoIpResolver::class)->countryFor('127.0.0.1'))->toBeNull()
        ->and(app(GeoIpResolver::class)->countryFor('192.168.1.10'))->toBeNull()
        ->and(app(GeoIpResolver::class)->countryFor('not-an-ip'))->toBeNull();

    Http::assertNothingSent();
});

it('returns null and does not throw when the provider errors', function () {
    Http::fake(['ipwho.is/*' => Http::response('boom', 500)]);

    expect(app(GeoIpResolver::class)->countryFor('8.8.8.8'))->toBeNull();
});

it('is a no-op when disabled', function () {
    config()->set('services.geoip.enabled', false);
    Http::fake();

    expect(app(GeoIpResolver::class)->countryFor('8.8.8.8'))->toBeNull();
    Http::assertNothingSent();
});

it('sets the visitor currency from an IP lookup when there is no CDN header', function () {
    Http::fake(['ipwho.is/*' => Http::response(['country_code' => 'FR'])]);

    $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->get('/_health/geo')
        ->assertOk()
        ->assertJsonPath('currency_source', 'géolocalisation')
        ->assertJsonPath('detected_currency', 'EUR');
});

it('lets a manual currency choice win over the IP lookup', function () {
    Http::fake(['ipwho.is/*' => Http::response(['country_code' => 'FR'])]);

    $this->get('/devise/SAR');

    $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->get('/_health/geo')
        ->assertJsonPath('detected_currency', 'SAR')
        ->assertJsonPath('currency_source', 'manuel (/devise)');
});
