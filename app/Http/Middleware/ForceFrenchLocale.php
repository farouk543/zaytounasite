<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceFrenchLocale
{
    public function handle(Request $request, Closure $next)
    {
        app()->setLocale('fr');
        return $next($request);
    }
}