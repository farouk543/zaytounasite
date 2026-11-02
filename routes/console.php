<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Statistiques du site : supprime les pages vues / clics de plus de 25 mois (voir PageView::prunable).
\Illuminate\Support\Facades\Schedule::command('model:prune', [
    '--model' => [\App\Models\PageView::class, \App\Models\ClickEvent::class],
])->daily();
