<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use App\Services\Analytics\VisitorTracker;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enregistre chaque page HTML vue par un visiteur humain (hors back-office, robots
 * et administrateurs). Ne bloque jamais le site : toute erreur est seulement loguée.
 */
class TrackPageView
{
    private const IGNORED_PREFIXES = [
        'admin', 'livewire', 'filament', '_health', 'lang/', 'devise/', 'up', 't/',
        'storage/', 'build/', 'sitemap.xml', 'robots.txt',
    ];

    public function __construct(private readonly VisitorTracker $tracker) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            if ($this->shouldTrack($request, $response)) {
                $this->record($request, $response);
            }
        } catch (\Throwable $e) {
            Log::warning('Analytics: page view not recorded', ['error' => $e->getMessage()]);
        }

        return $response;
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') || $response->getStatusCode() !== 200) {
            return false;
        }

        if ($request->ajax() || $request->header('X-Livewire') || $request->header('Purpose') === 'prefetch'
            || $request->header('Sec-Purpose') === 'prefetch') {
            return false;
        }

        if (! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return false;
        }

        $path = ltrim($request->path(), '/');
        foreach (self::IGNORED_PREFIXES as $prefix) {
            if ($path === rtrim($prefix, '/') || str_starts_with($path, $prefix)) {
                return false;
            }
        }

        if ($request->user()?->hasRole('admin')) {
            return false;
        }

        return ! $this->tracker->isBot($request);
    }

    private function record(Request $request, Response $response): void
    {
        $agent = $this->tracker->agent($request);
        $source = $this->tracker->source($request);

        PageView::create([
            'visitor_hash' => $this->tracker->visitorHash($request),
            'path' => Str::limit('/'.ltrim($request->path(), '/'), 495, ''),
            'route_name' => $request->route()?->getName(),
            'title' => $this->extractTitle((string) $response->getContent()),
            'referrer_host' => $source['referrer_host'] ? Str::limit($source['referrer_host'], 185, '') : null,
            'source' => $source['source'],
            'utm_source' => $this->param($request, 'utm_source'),
            'utm_medium' => $this->param($request, 'utm_medium'),
            'utm_campaign' => $this->param($request, 'utm_campaign', 150),
            'country' => $this->tracker->country($request),
            'device' => $agent['device'],
            'browser' => $agent['browser'],
            'os' => $agent['os'],
            'locale' => app()->getLocale(),
            'user_id' => $request->user()?->id,
        ]);
    }

    private function extractTitle(string $html): ?string
    {
        if (! preg_match('/<title[^>]*>(.*?)<\/title>/is', substr($html, 0, 6000), $m)) {
            return null;
        }

        $title = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return $title !== '' ? Str::limit($title, 195, '') : null;
    }

    private function param(Request $request, string $key, int $max = 100): ?string
    {
        $value = trim((string) $request->query($key, ''));

        return $value !== '' ? Str::limit($value, $max - 5, '') : null;
    }
}
