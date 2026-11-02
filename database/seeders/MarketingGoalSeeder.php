<?php

namespace Database\Seeders;

use App\Models\MarketingGoal;
use Illuminate\Database\Seeder;

/**
 * Objectifs de lancement (novembre 2026 → janvier 2027).
 *
 * Il n'existe pas encore d'historique de trafic : ces cibles sont des hypothèses de départ
 * (+30 %/mois), à réajuster après le premier bilan mensuel dans « Objectifs marketing ».
 * Idempotent : un objectif déjà présent (même mois, indicateur, pays) n'est jamais modifié.
 */
class MarketingGoalSeeder extends Seeder
{
    /** Cibles de novembre 2026 ; les mois suivants progressent de 30 %. */
    private const BASE = [
        // Tous pays confondus
        ['visitors', null, 600],
        ['google_visitors', null, 120],
        ['blog_readers', null, 150],
        ['signups', null, 25],
        ['conversion', null, 4],
        ['whatsapp', null, 30],
        ['orders', null, 10],
        ['paid', null, 6],
        // Plateforme : garder les élèves (taux, sans historique nécessaire)
        ['retention', null, 80],
        ['attendance', null, 90],
        // Marchés prioritaires : visiteurs…
        ['visitors', 'TN', 250],
        ['visitors', 'FR', 130],
        ['visitors', 'SA', 60],
        ['visitors', 'QA', 50],
        ['visitors', 'CA', 40],
        // … et inscriptions
        ['signups', 'TN', 10],
        ['signups', 'FR', 6],
        ['signups', 'SA', 3],
        ['signups', 'QA', 3],
        ['signups', 'CA', 2],
    ];

    /** Rythme éditorial : calé sur le calendrier de BlogArticlesSeeder. */
    private const ARTICLES = ['2026-11' => 4, '2026-12' => 4, '2027-01' => 3];

    public function run(): void
    {
        $created = 0;

        foreach (array_keys(self::ARTICLES) as $i => $month) {
            $factor = 1.3 ** $i;

            foreach (self::BASE as [$metric, $country, $target]) {
                $isRate = MarketingGoal::METRICS[$metric]['unit'] === '%';
                $goal = MarketingGoal::firstOrCreate(
                    ['month' => $month, 'metric' => $metric, 'country' => $country],
                    ['target' => $isRate ? $target : (int) ceil($target * $factor)],
                );
                $created += $goal->wasRecentlyCreated ? 1 : 0;
            }

            $goal = MarketingGoal::firstOrCreate(
                ['month' => $month, 'metric' => 'articles', 'country' => null],
                ['target' => self::ARTICLES[$month], 'notes' => 'Calendrier éditorial du blog.'],
            );
            $created += $goal->wasRecentlyCreated ? 1 : 0;
        }

        $this->command?->info("Objectifs : {$created} objectif(s) créé(s).");
    }
}
