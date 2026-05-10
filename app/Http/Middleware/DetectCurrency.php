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
        $country  = CurrencyService::detectCountry($request);
        $currency = CurrencyService::getCurrencyForCountry($country);

        if (
            session('app_country') !== $country ||
            session('app_currency') !== $currency
        ) {
            session([
                'app_country'  => $country,
                'app_currency' => $currency,
            ]);
        }

        return $next($request);
    }
}