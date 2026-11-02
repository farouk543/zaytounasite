<?php

namespace App\Services\Analytics;

use Illuminate\Http\Request;

/**
 * Analyse anonyme d'une requête : identifiant visiteur sans cookie, robot ou humain,
 * appareil / navigateur / OS et source de trafic.
 */
class VisitorTracker
{
    private const BOT_PATTERN = '/bot|crawl|spider|slurp|bingpreview|mediapartners|facebookexternalhit|whatsapp|telegrambot|'
        .'embedly|quora link|pinterest|lighthouse|pagespeed|headless|phantomjs|puppeteer|playwright|selenium|'
        .'curl|wget|python|go-http|java\/|okhttp|axios|node-fetch|guzzle|httpclient|scrapy|ahrefs|semrush|'
        .'mj12|dotbot|petalbot|yandex|baidu|bytespider|gptbot|claudebot|ccbot|perplexity|uptime|monitor|preview/i';

    /** Domaine du référent → source lisible. L'ordre compte (premier trouvé). */
    private const SOURCES = [
        'google' => 'Google',
        'bing' => 'Bing',
        'yahoo' => 'Yahoo',
        'duckduckgo' => 'DuckDuckGo',
        'ecosia' => 'Ecosia',
        'qwant' => 'Qwant',
        'yandex' => 'Yandex',
        'facebook' => 'Facebook',
        'fb.' => 'Facebook',
        'messenger' => 'Facebook',
        'instagram' => 'Instagram',
        'youtube' => 'YouTube',
        'youtu.be' => 'YouTube',
        'tiktok' => 'TikTok',
        'whatsapp' => 'WhatsApp',
        'wa.me' => 'WhatsApp',
        'linkedin' => 'LinkedIn',
        'lnkd.in' => 'LinkedIn',
        't.co' => 'X / Twitter',
        'twitter' => 'X / Twitter',
        'x.com' => 'X / Twitter',
        'snapchat' => 'Snapchat',
        'pinterest' => 'Pinterest',
        'telegram' => 'Telegram',
        't.me' => 'Telegram',
        'chatgpt' => 'ChatGPT',
        'openai' => 'ChatGPT',
        'perplexity' => 'Perplexity',
        'mail.' => 'E-mail',
        'outlook' => 'E-mail',
    ];

    public function isBot(Request $request): bool
    {
        $ua = (string) $request->userAgent();

        return $ua === '' || strlen($ua) < 20 || preg_match(self::BOT_PATTERN, $ua) === 1;
    }

    /**
     * Identifiant anonyme stable pendant un mois (sel = clé de l'app + mois).
     * Permet de compter les visiteurs uniques du mois sans cookie ni IP stockée.
     */
    public function visitorHash(Request $request): string
    {
        $salt = config('app.key').'|'.now()->format('Y-m');

        return hash('sha256', $salt.'|'.$this->clientIp($request).'|'.$request->userAgent());
    }

    public function clientIp(Request $request): string
    {
        $cf = (string) $request->header('CF-Connecting-IP', '');

        return filter_var($cf, FILTER_VALIDATE_IP) ? $cf : (string) $request->ip();
    }

    public function country(Request $request): ?string
    {
        $cf = strtoupper((string) $request->header('CF-IPCountry', ''));

        if (strlen($cf) === 2 && ctype_alpha($cf) && $cf !== 'XX') {
            return $cf;
        }

        $session = $request->hasSession() ? $request->session()->get('app_country') : null;

        return is_string($session) && strlen($session) === 2 ? strtoupper($session) : null;
    }

    /** @return array{device: string, browser: string, os: string} */
    public function agent(Request $request): array
    {
        $ua = (string) $request->userAgent();

        $device = match (true) {
            (bool) preg_match('/ipad|tablet|kindle|silk|(android(?!.*mobile))/i', $ua) => 'tablet',
            (bool) preg_match('/mobi|iphone|ipod|android|blackberry|opera mini|iemobile/i', $ua) => 'mobile',
            default => 'desktop',
        };

        $browser = match (true) {
            (bool) preg_match('/FBAN|FBAV|Instagram/i', $ua) => 'App Facebook/Insta',
            (bool) preg_match('/Edg\//i', $ua) => 'Edge',
            (bool) preg_match('/OPR\/|Opera/i', $ua) => 'Opera',
            (bool) preg_match('/SamsungBrowser/i', $ua) => 'Samsung Internet',
            (bool) preg_match('/Firefox|FxiOS/i', $ua) => 'Firefox',
            (bool) preg_match('/Chrome|CriOS/i', $ua) => 'Chrome',
            (bool) preg_match('/Safari/i', $ua) => 'Safari',
            default => 'Autre',
        };

        $os = match (true) {
            (bool) preg_match('/iPhone|iPad|iPod/i', $ua) => 'iOS',
            (bool) preg_match('/Android/i', $ua) => 'Android',
            (bool) preg_match('/Windows/i', $ua) => 'Windows',
            (bool) preg_match('/Mac OS X|Macintosh/i', $ua) => 'macOS',
            (bool) preg_match('/Linux/i', $ua) => 'Linux',
            default => 'Autre',
        };

        return compact('device', 'browser', 'os');
    }

    /**
     * Source de trafic : UTM en priorité, sinon domaine du référent.
     *
     * @return array{source: string, referrer_host: ?string}
     */
    public function source(Request $request): array
    {
        $utm = trim((string) $request->query('utm_source', ''));
        $referrer = (string) $request->headers->get('referer', '');
        $host = $referrer !== '' ? strtolower((string) parse_url($referrer, PHP_URL_HOST)) : null;
        $host = $host ? preg_replace('/^www\./', '', $host) : null;

        if ($utm !== '') {
            return ['source' => $this->classify(strtolower($utm)) ?? ucfirst(substr($utm, 0, 30)), 'referrer_host' => $host];
        }

        if (! $host) {
            return ['source' => 'Direct', 'referrer_host' => null];
        }

        $ownHost = preg_replace('/^www\./', '', strtolower((string) $request->getHost()));

        if ($host === $ownHost) {
            return ['source' => 'Interne', 'referrer_host' => $host];
        }

        return ['source' => $this->classify($host) ?? 'Autre site', 'referrer_host' => $host];
    }

    private function classify(string $haystack): ?string
    {
        foreach (self::SOURCES as $needle => $label) {
            if (str_contains($haystack, $needle)) {
                return $label;
            }
        }

        return null;
    }
}
