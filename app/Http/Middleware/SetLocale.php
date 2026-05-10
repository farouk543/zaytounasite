<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    private array $available = ['fr', 'en', 'ar'];

    public function handle(Request $request, Closure $next)
    {
        // 1) Si user a choisi une langue -> session
        $sessionLocale = session('locale');
        if (is_string($sessionLocale) && in_array($sessionLocale, $this->available, true)) {
            app()->setLocale($sessionLocale);
            return $next($request);
        }

        // 2) Sinon auto-detect via navigateur
        $preferred = $request->getPreferredLanguage($this->available);

        // 3) Fallback config/app.php (ex: fr)
        $locale = (is_string($preferred) && in_array($preferred, $this->available, true))
            ? $preferred
            : config('app.locale', 'fr');

        app()->setLocale($locale);

        return $next($request);
    }
}