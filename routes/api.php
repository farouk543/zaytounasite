<?php

use App\Http\Controllers\Api\SiteStatsController;
use Illuminate\Support\Facades\Route;

// Statistiques agrégées pour l'admin de la plateforme d'enseignement (jeton Bearer partagé).
Route::get('/site/stats', SiteStatsController::class)
    ->middleware('throttle:30,1')
    ->name('api.site.stats');
