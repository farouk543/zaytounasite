<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Server-side "which country is this IP in?" — the fallback used when no
 * CDN/proxy geolocation header is available (the site is not behind
 * Cloudflare). Calls a free, keyless HTTP provider and caches the result
 * per IP. Every failure is swallowed: geolocation is best-effort and must
 * never block or break a page.
 */
class GeoIpResolver
{
    /**
     * ISO-3166 alpha-2 country code for an IP, or null when it can't be
     * determined (disabled, private IP, provider error, timeout…).
     */
    public function countryFor(?string $ip): ?string
    {
        if (! config('services.geoip.enabled')) {
            return null;
        }

        if (! is_string($ip) || $ip === '' || ! $this->isPublicIp($ip)) {
            return null;
        }

        $cacheKey = 'geoip:country:'.$ip;

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey) ?: null;
        }

        $country = $this->lookup($ip);

        // Cache hits for a long time; cache misses briefly so a transient
        // provider outage doesn't pin every visitor to the default for weeks.
        Cache::put(
            $cacheKey,
            $country ?? '',
            $country ? now()->addDays((int) config('services.geoip.cache_days', 30)) : now()->addHours(6)
        );

        return $country;
    }

    private function lookup(string $ip): ?string
    {
        try {
            $url = str_replace('{ip}', $ip, (string) config('services.geoip.endpoint'));

            $response = Http::timeout((float) config('services.geoip.timeout', 1.5))
                ->acceptJson()
                ->get($url);

            if (! $response->ok()) {
                return null;
            }

            $code = data_get($response->json(), (string) config('services.geoip.country_path', 'country_code'));

            return (is_string($code) && strlen($code) === 2 && ctype_alpha($code))
                ? strtoupper($code)
                : null;
        } catch (\Throwable $e) {
            Log::debug('GeoIpResolver lookup failed', ['ip' => $ip, 'error' => $e->getMessage()]);

            return null;
        }
    }

    private function isPublicIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }
}
