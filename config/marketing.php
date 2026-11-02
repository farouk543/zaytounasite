<?php

/*
|--------------------------------------------------------------------------
| Marchés prioritaires (« pays de vision »)
|--------------------------------------------------------------------------
| Les pays visés en priorité par le blog, le SEO et les objectifs du back-office.
| L'ordre compte : c'est l'ordre d'affichage partout.
|
| - hreflang : variantes régionales annoncées à Google pour chaque langue du site
|   (ex. fr-CA signale que la version française vise aussi le Canada).
| - timezone : sert aux articles (créneaux de cours) et aux recommandations.
*/

return [

    'markets' => [
        'TN' => ['name' => 'Tunisie', 'flag' => '🇹🇳', 'hreflang' => ['fr' => 'fr-TN', 'ar' => 'ar-TN'], 'timezone' => 'Africa/Tunis'],
        'FR' => ['name' => 'France', 'flag' => '🇫🇷', 'hreflang' => ['fr' => 'fr-FR'], 'timezone' => 'Europe/Paris'],
        'SA' => ['name' => 'Arabie saoudite', 'flag' => '🇸🇦', 'hreflang' => ['ar' => 'ar-SA', 'en' => 'en-SA'], 'timezone' => 'Asia/Riyadh'],
        'QA' => ['name' => 'Qatar', 'flag' => '🇶🇦', 'hreflang' => ['ar' => 'ar-QA', 'en' => 'en-QA'], 'timezone' => 'Asia/Qatar'],
        'CA' => ['name' => 'Canada', 'flag' => '🇨🇦', 'hreflang' => ['fr' => 'fr-CA', 'en' => 'en-CA'], 'timezone' => 'America/Toronto'],
    ],

    // Fuseau utilisé pour les dates du back-office et la publication des articles.
    'timezone' => 'Africa/Tunis',

];
