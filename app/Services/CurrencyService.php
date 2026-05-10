<?php

namespace App\Services;

use Illuminate\Http\Request;

class CurrencyService
{
    /** Exchange rates relative to TND (1 TND = X currency) */
    public const RATES_FROM_TND = [
        'TND' => 1.00,
        'EUR' => 0.74,
        'USD' => 0.32,
        'GBP' => 0.25,
        'QAR' => 2.90,
        'SAR' => 2.00,
        'AED' => 1.17,
        'MAD' => 3.20,
    ];

    /**
     * Fixed pack prices per currency (exact business prices).
     * Fallback to rate conversion for currencies not listed.
     */
    public const PACK_PRICES = [
        'individual' => ['TND' => 200, 'EUR' => 149, 'SAR' => 399, 'QAR' => 579],
        'duo'        => ['TND' => 120, 'EUR' => 89,  'SAR' => 239, 'QAR' => 349],
        'group'      => ['TND' => 80,  'EUR' => 59,  'SAR' => 159, 'QAR' => 229],
        'recorded'   => ['TND' => 90,  'EUR' => 67,  'SAR' => 179, 'QAR' => 259],
        'hour'       => ['TND' => 30,  'EUR' => 22,  'SAR' => 59,  'QAR' => 89 ],
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
     * Return the fixed price for a pack in the given currency.
     * Falls back to TND→currency conversion if not explicitly listed.
     */
    public static function packPrice(string $pack, string $currency): int
    {
        $prices = self::PACK_PRICES[$pack] ?? null;
        if (!$prices) return 0;

        if (isset($prices[$currency])) {
            return (int) $prices[$currency];
        }

        $tnd = $prices['TND'] ?? 0;
        return (int) round(self::convert($tnd, 'TND', $currency));
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
