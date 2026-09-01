<?php

namespace App\Services;

use Illuminate\Http\Request;

class CurrencyService
{
    /**
     * Currencies the store can display and bill in.
     * Every one of these must appear in RATES_FROM_TND and SYMBOLS.
     */
    public const SUPPORTED = ['TND', 'EUR', 'USD', 'GBP', 'QAR', 'SAR', 'AED', 'MAD'];

    /**
     * Exchange rates relative to TND (1 TND = X currency).
     *
     * Conversion fallback only — explicit per-market prices (PACK_PRICES /
     * product_prices) always win. Mid-market rates checked 2026-09-01;
     * Gulf values derived from the USD rate via each currency's USD peg
     * (QAR 3.64, SAR 3.75, AED 3.6725). Refresh periodically.
     */
    public const RATES_FROM_TND = [
        'TND' => 1.00,
        'EUR' => 0.295,
        'USD' => 0.342,
        'GBP' => 0.253,
        'QAR' => 1.245,
        'SAR' => 1.283,
        'AED' => 1.256,
        'MAD' => 3.190,
    ];

    /**
     * Rounding step applied to auto-converted amounts, per currency.
     * Keeps estimated prices from showing ugly conversion decimals.
     */
    public const ROUNDING_STEP = [
        'TND' => 1,
        'EUR' => 1,
        'USD' => 1,
        'GBP' => 1,
        'QAR' => 5,
        'SAR' => 5,
        'AED' => 5,
        'MAD' => 10,
    ];

    /**
     * Pack prices.
     *
     * TND is the source of truth (prices set for the Tunisian market).
     * EUR is locked to the historical business prices ("l'UE a déjà le bon prix").
     * Every other market is DERIVED at runtime by packPrice(): the Tunisian
     * base price is scaled by MARKET_PPP_MULTIPLIER (local charges + wage
     * level relative to Tunisia), then FX-converted and rounded.
     */
    public const PACK_PRICES = [
        'individual' => ['TND' => 200, 'EUR' => 149],
        'duo'        => ['TND' => 120, 'EUR' => 89],
        'group'      => ['TND' => 80,  'EUR' => 59],
        'recorded'   => ['TND' => 90,  'EUR' => 67],
        'hour'       => ['TND' => 30,  'EUR' => 22],
    ];

    /**
     * Purchasing-power multiplier per market, relative to Tunisia (= 1.00).
     *
     * Reflects local salary level and cost/charges vs Tunisia: a pack that
     * costs 200 TND in Tunisia should weigh a comparable share of a local
     * salary elsewhere. Calibrated so EUR lands on the historical prices
     * (200 TND × 2.53 × FX(TND→EUR) ≈ 149 €); other markets scaled the
     * same way from their income level. Refine with real payroll data.
     */
    public const MARKET_PPP_MULTIPLIER = [
        'TND' => 1.00,
        'EUR' => 2.53,
        'USD' => 2.55,
        'GBP' => 2.65,
        'QAR' => 3.00,
        'SAR' => 2.35,
        'AED' => 2.80,
        'MAD' => 1.05,
    ];

    /** Country code → preferred display currency */
    public const COUNTRY_CURRENCY = [
        'TN' => 'TND',
        'FR' => 'EUR',
        'BE' => 'EUR',
        'LU' => 'EUR',
        'CH' => 'EUR',
        'DE' => 'EUR',
        'IT' => 'EUR',
        'ES' => 'EUR',
        'NL' => 'EUR',
        'AT' => 'EUR',
        'PT' => 'EUR',
        'GB' => 'GBP',
        'US' => 'USD',
        'CA' => 'USD',
        'AU' => 'USD',
        'QA' => 'QAR',
        'SA' => 'SAR',
        'AE' => 'AED',
        'MA' => 'MAD',
        'DZ' => 'EUR',
        'LY' => 'USD',
    ];

    /** Currency symbols for display */
    public const SYMBOLS = [
        'TND' => 'DT',
        'EUR' => '€',
        'USD' => '$',
        'GBP' => '£',
        'QAR' => 'QAR',
        'SAR' => 'SAR',
        'AED' => 'AED',
        'MAD' => 'MAD',
    ];

    /** Native currency per regime */
    public const REGIME_CURRENCY = [
        'tunisia' => 'TND',
        'qatar'   => 'QAR',
        'saudi'   => 'SAR',
        'quran'   => 'TND',
    ];

    /** Hour prices per regime in native currency */
    public const REGIME_HOUR_PRICE = [
        'tunisia' => 30,
        'qatar'   => 35,
        'saudi'   => 35,
        'quran'   => 30,
    ];

    public static function detectCountry(Request $request): string
    {
        // Cloudflare proxy sets CF-IPCountry based on real visitor IP
        $cfCountry = $request->header('CF-IPCountry', '');
        if ($cfCountry && strlen($cfCountry) === 2 && ctype_alpha($cfCountry) && $cfCountry !== 'XX') {
            return strtoupper($cfCountry);
        }

        // Sans Cloudflare : on ne devine pas depuis Accept-Language car un
        // navigateur fr-FR ne signifie pas que l'utilisateur est en France.
        // Défaut = TN (marché principal).
        return 'TN';
    }

    public static function getCurrencyForCountry(string $country): string
    {
        return self::COUNTRY_CURRENCY[strtoupper($country)] ?? 'TND';
    }

    public static function isSupported(string $currency): bool
    {
        return in_array(strtoupper($currency), self::SUPPORTED, true);
    }

    /**
     * The currency the visitor is currently browsing in.
     * Resolved from the session (set by DetectCurrency middleware or the
     * manual /devise/{code} switch); falls back to TND.
     */
    public static function current(): string
    {
        $currency = strtoupper((string) session('app_currency', 'TND'));

        return self::isSupported($currency) ? $currency : 'TND';
    }

    /**
     * Whether the current currency comes from a real signal — the visitor
     * picked it, or geolocation resolved it — rather than the bare default.
     * Regime pages use this to decide between the visitor currency and the
     * regime's own billing currency.
     */
    public static function hasExplicitChoice(): bool
    {
        return (bool) (session('app_currency_manual') || session('app_currency_resolved'));
    }

    /**
     * Round an auto-converted amount to a clean value for its market,
     * using ROUNDING_STEP (e.g. nearest 5 QAR, nearest 10 MAD).
     */
    public static function roundForMarket(float $amount, string $currency): int
    {
        $step = self::ROUNDING_STEP[strtoupper($currency)] ?? 1;
        $step = max(1, $step);

        return (int) (round($amount / $step) * $step);
    }

    /**
     * Convert amount from one currency to another via TND as pivot.
     */
    public static function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        $rates = self::RATES_FROM_TND;
        $fromRate = $rates[$from] ?? 1.0;
        $toRate   = $rates[$to]   ?? 1.0;

        // amount in TND = amount / fromRate
        // amount in target = amountInTnd * toRate
        return ($amount / $fromRate) * $toRate;
    }

    public static function format(float $amount, string $currency): string
    {
        $symbol = self::SYMBOLS[$currency] ?? $currency;
        return number_format($amount, 0) . ' ' . $symbol;
    }

    /**
     * Price for a pack in the given currency.
     *
     * TND / EUR come straight from PACK_PRICES. Every other market is
     * derived from the Tunisian base price scaled by its purchasing-power
     * multiplier (MARKET_PPP_MULTIPLIER), FX-converted, then rounded to a
     * clean market value.
     */
    public static function packPrice(string $pack, string $currency): int
    {
        $prices = self::PACK_PRICES[$pack] ?? null;
        if (! $prices) {
            return 0;
        }

        $currency = strtoupper($currency);

        if (isset($prices[$currency])) {
            return (int) $prices[$currency];
        }

        return self::marketAdaptedPrice((float) ($prices['TND'] ?? 0), $currency);
    }

    /**
     * Adapt an arbitrary Tunisian price to another market: scale by the
     * purchasing-power multiplier, FX-convert, then round to a clean value.
     * Same model as packPrice() but for one-off amounts (Club d'été, etc.).
     */
    public static function marketAdaptedPrice(float $tndAmount, string $currency): int
    {
        $currency = strtoupper($currency);

        if ($currency === 'TND' || ! self::isSupported($currency)) {
            return (int) round($tndAmount);
        }

        $ppp = self::MARKET_PPP_MULTIPLIER[$currency] ?? 1.0;

        return self::roundForMarket(self::convert($tndAmount * $ppp, 'TND', $currency), $currency);
    }

    /**
     * Return a formatted pack price string (e.g. "149 € / mois").
     */
    public static function formatPack(string $pack, string $currency, string $period = 'monthly'): string
    {
        $amount = self::packPrice($pack, $currency);
        $symbol = self::SYMBOLS[$currency] ?? $currency;
        $suffix = match ($period) {
            'quarterly' => '/ trimestre',
            'yearly'    => '/ an',
            default     => '/ mois',
        };
        return number_format($amount, 0) . ' ' . $symbol . ' ' . $suffix;
    }

    /**
     * Return per-mode hourly prices for the custom pack builder,
     * scaled to the visitor's currency from the fixed TND base rates.
     * Base TND: individual=50, duo=38, group=30.
     */
    public static function modeHourPrices(string $currency): array
    {
        $tndBase = ['individual' => 50, 'duo' => 38, 'group' => 30];
        $tndHour = self::PACK_PRICES['hour']['TND']; // 30

        if ($currency === 'TND') {
            return $tndBase;
        }

        $targetHour = self::packPrice('hour', $currency);
        $result = [];
        foreach ($tndBase as $mode => $tndVal) {
            $result[$mode] = (int) round($targetHour * $tndVal / $tndHour);
        }
        return $result;
    }
}
