<?php

use App\Http\Middleware\DetectCurrency;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackPageView;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // ✅ Locale only for web routes (session-based)
        $middleware->web(append: [
            SetLocale::class,
            SecurityHeaders::class,
            DetectCurrency::class,
            TrackPageView::class,
        ]);

        // (optionnel) si tu veux vraiment en global, mais ici non recommandé.
        // $middleware->append(...);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
