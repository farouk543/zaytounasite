<?php

use App\Models\ClickEvent;
use App\Models\PageView;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Statistiques du site : supprime les pages vues / clics de plus de 25 mois (voir PageView::prunable).
Schedule::command('model:prune', [
    '--model' => [PageView::class, ClickEvent::class],
])->daily();

// Bilan marketing du mois écoulé, par e-mail aux admins, le 1er de chaque mois à 8 h (Tunis).
Schedule::command('marketing:monthly-report')
    ->monthlyOn(1, '08:00')->timezone('Africa/Tunis');

// Avis publics des élèves de la plateforme → témoignages à valider (jamais publiés automatiquement).
Schedule::command('testimonials:import-platform')
    ->weeklyOn(1, '09:00')->timezone('Africa/Tunis');
