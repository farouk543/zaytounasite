<?php

namespace App\Http\Middleware;

use App\Services\CurrencyService;
use App\Services\GeoIpResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectCurrency
{
    public function __construct(private readonly GeoIpResolver $geoIp) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Visitor picked a currency by hand → never override it.
        if (session('app_currency_manual')) {
            return $next($request);
        }

        // Already resolved earlier in this session → nothing to do.
        if (session('app_currency_resolved')) {
            return $next($request);
        }

        // 1. Proxy/CDN geolocation header (Cloudflare CF-IPCountry, …).
        $cf = (string) $request->header('CF-IPCountry', '');

        if (strlen($cf) === 2 && ctype_alpha($cf) && strtoupper($cf) !== 'XX') {
            $this->apply(strtoupper($cf));

            return $next($request);
        }

        // 2. Server-side IP lookup, attempted once per session.
        if (! session('app_geoip_tried')) {
            session(['app_geoip_tried' => true]);

            $country = $this->geoIp->countryFor($this->clientIp($request));

            if ($country !== null) {
                $this->apply($country);
            }
        }

        return $next($request);
    }

    /**
     * Best-effort real visitor IP. Uses REMOTE_ADDR unless it is private
     * (shared-hosting LB hop), in which case the first public address in
     * X-Forwarded-For is used.
     */
    private function clientIp(Request $request): ?string
    {
        $ip = $request->ip();

        $isPublic = fn (?string $candidate) => is_string($candidate) && filter_var(
            trim($candidate),
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;

        if ($isPublic($ip)) {
            return $ip;
        }

        foreach (explode(',', (string) $request->header('X-Forwarded-For', '')) as $candidate) {
            if ($isPublic($candidate)) {
                return trim($candidate);
            }
        }

        return $ip;
    }

    private function apply(string $country): void
    {
        session([
            'app_country' => $country,
            'app_currency' => CurrencyService::getCurrencyForCountry($country),
            'app_currency_resolved' => true,
        ]);
    }
}
