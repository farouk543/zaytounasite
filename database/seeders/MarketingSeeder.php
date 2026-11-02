<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Tout le volet marketing en une commande (sans toucher aux comptes) :
 *   php artisan db:seed --class=MarketingSeeder --force
 *
 * Catégories du blog, articles programmés et objectifs de lancement. Idempotent.
 */
class MarketingSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BlogArticlesSeeder::class,   // appelle aussi BlogCategorySeeder
            MarketingGoalSeeder::class,
        ]);
    }
}
