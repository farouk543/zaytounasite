<?php

use App\Services\CurrencyService;

test('isSupported knows the store currencies', function () {
    expect(CurrencyService::isSupported('eur'))->toBeTrue()
        ->and(CurrencyService::isSupported('USD'))->toBeTrue()
        ->and(CurrencyService::isSupported('JPY'))->toBeFalse();
});

test('convert uses TND as pivot and is identity for same currency', function () {
    expect(CurrencyService::convert(100.0, 'EUR', 'EUR'))->toBe(100.0);

    $tndToEur = CurrencyService::convert(100.0, 'TND', 'EUR');
    expect($tndToEur)->toEqualWithDelta(100.0 * CurrencyService::RATES_FROM_TND['EUR'], 0.0001);
});

test('roundForMarket snaps to the market step', function () {
    // EUR step = 1
    expect(CurrencyService::roundForMarket(149.4, 'EUR'))->toBe(149);
    // QAR step = 5
    expect(CurrencyService::roundForMarket(147.0, 'QAR'))->toBe(145);
    expect(CurrencyService::roundForMarket(148.0, 'QAR'))->toBe(150);
    // MAD step = 10
    expect(CurrencyService::roundForMarket(1594.0, 'MAD'))->toBe(1590);
});

test('packPrice returns the explicit TND and EUR prices verbatim', function () {
    expect(CurrencyService::packPrice('individual', 'TND'))->toBe(200)
        ->and(CurrencyService::packPrice('individual', 'EUR'))->toBe(149);
});

test('packPrice derives other markets from the Tunisian base via the PPP multiplier', function () {
    $tnd = CurrencyService::PACK_PRICES['individual']['TND'];
    $ppp = CurrencyService::MARKET_PPP_MULTIPLIER['SAR'];

    $expected = CurrencyService::roundForMarket(
        CurrencyService::convert($tnd * $ppp, 'TND', 'SAR'),
        'SAR'
    );

    expect(CurrencyService::packPrice('individual', 'SAR'))->toBe($expected)
        ->and($expected)->toBeGreaterThan(0);
});

test('EUR pack price stays calibrated near the PPP derivation', function () {
    $tnd = CurrencyService::PACK_PRICES['individual']['TND'];
    $derived = CurrencyService::convert($tnd * CurrencyService::MARKET_PPP_MULTIPLIER['EUR'], 'TND', 'EUR');

    // The locked EUR price should be within a few euros of the formula.
    expect(abs($derived - 149))->toBeLessThan(6.0);
});
