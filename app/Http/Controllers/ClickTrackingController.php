<?php

namespace App\Http\Controllers;

use App\Models\ClickEvent;
use App\Services\Analytics\VisitorTracker;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Reçoit les clics envoyés par le petit script du layout (navigator.sendBeacon).
 */
class ClickTrackingController extends Controller
{
    public function store(Request $request, VisitorTracker $tracker): Response
    {
        if ($tracker->isBot($request) || $request->user()?->hasRole('admin')) {
            return response()->noContent();
        }

        $data = $request->validate([
            'p' => ['required', 'string', 'max:500'],
            'l' => ['required', 'string', 'max:300'],
            'h' => ['nullable', 'string', 'max:1000'],
            'k' => ['nullable', 'string', 'in:link,button,external,cta'],
        ]);

        $label = trim(preg_replace('/\s+/u', ' ', strip_tags($data['l'])));
        if ($label === '') {
            return response()->noContent();
        }

        ClickEvent::create([
            'visitor_hash' => $tracker->visitorHash($request),
            'page_path' => Str::limit('/'.ltrim(parse_url($data['p'], PHP_URL_PATH) ?: '/', '/'), 495, ''),
            'label' => Str::limit($label, 145, ''),
            'target' => isset($data['h']) ? Str::limit($this->cleanTarget($request, $data['h']), 495, '') : null,
            'kind' => $data['k'] ?? 'link',
            'device' => $tracker->agent($request)['device'],
        ]);

        return response()->noContent();
    }

    /** Lien interne → chemin seul ; lien externe → domaine + chemin (sans paramètres). */
    private function cleanTarget(Request $request, string $href): string
    {
        $parts = parse_url($href);
        $host = $parts['host'] ?? null;
        $path = $parts['path'] ?? '/';

        if (! $host || $host === $request->getHost()) {
            return $path;
        }

        return $host.$path;
    }
}
