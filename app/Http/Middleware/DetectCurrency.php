<?php

namespace App\Http\Middleware;

use App\Services\CurrencyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectCurrency
{
    public function handle(Request $request, Closure $next): Response
    {
        // Visitor picked a currency by hand → never override it.
        if (session('app_currency_manual')) {
            return $next($request);
        }

        // Only act on a real geolocation signal (Cloudflare CF-IPCountry).
        // Without it we leave the session untouched so downstream code can
        // fall back to a sensible default (TND, or the regime currency).
        $cf = (string) $request->header('CF-IPCountry', '');

        if (strlen($cf) !== 2 || ! ctype_alpha($cf) || strtoupper($cf) === 'XX') {
            return $next($request);
        }

        $country  = strtoupper($cf);
        $currency = CurrencyService::getCurrencyForCountry($country);

        if (
            session('app_country') !== $country ||
            session('app_currency') !== $currency ||
            ! session('app_currency_resolved')
        ) {
            session([
                'app_country'           => $country,
                'app_currency'          => $currency,
                'app_currency_resolved' => true,
            ]);
        }

        return $next($request);
    }
}
