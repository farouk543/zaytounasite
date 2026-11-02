<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Block the page from being embedded in iframes (clickjacking protection)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Enable browser XSS filter (legacy but harmless)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Control referrer info sent when navigating away
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Disable browser features that are not needed
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Content-Security-Policy « sûre » : ne touche pas aux scripts/styles (Alpine, Livewire, inline),
        // mais interdit l'intégration du site ailleurs, les plugins, le détournement de <base>
        // et l'envoi de formulaires vers un autre site.
        $response->headers->set('Content-Security-Policy',
            "frame-ancestors 'self'; base-uri 'self'; object-src 'none'; form-action 'self'");

        // Ne pas annoncer la version de PHP.
        $response->headers->remove('X-Powered-By');
        if (! headers_sent()) {
            header_remove('X-Powered-By');
        }

        // Force HTTPS (only in production to avoid local dev issues)
        if (app()->isProduction()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
