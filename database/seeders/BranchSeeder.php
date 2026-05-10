<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Track;
use App\Models\Level;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $tunisia = Track::where('slug', 'tunisia')->first();
        if (!$tunisia) return;

        $levels = Level::where('track_id', $tunisia->id)->get()->keyBy('slug');

        $data = [
            'lycee-11' => [
                ['slug' => 'sciences', 'name' => 'Sciences', 'sort_order' => 1],
                ['slug' => 'lettres', 'name' => 'Lettres', 'sort_order' => 2],
                ['slug' => 'eco-services', 'name' => 'Économie et Services', 'sort_order' => 3],
                ['slug' => 'informatique', 'name' => 'Technologie de l\'Informatique', 'sort_order' => 4],
            ],

            'lycee-12' => [
                ['slug' => 'math', 'name' => 'Mathématiques', 'sort_order' => 1],
                ['slug' => 'sciences-experimentales', 'name' => 'Sciences Expérimentales', 'sort_order' => 2],
                ['slug' => 'eco-gestion', 'name' => 'Économie et Gestion', 'sort_order' => 3],
                ['slug' => 'sciences-techniques', 'name' => 'Sciences Techniques', 'sort_order' => 4],
                ['slug' => 'sciences-informatiques', 'name' => 'Sciences de l\'Informatique', 'sort_order' => 5],
                ['slug' => 'lettres', 'name' => 'Lettres', 'sort_order' => 6],
            ],

            'baccalaureat' => [
                ['slug' => 'math', 'name' => 'Mathématiques', 'sort_order' => 1],
                ['slug' => 'sciences-experimentales', 'name' => 'Sciences Expérimentales', 'sort_order' => 2],
                ['slug' => 'eco-gestion', 'name' => 'Économie et Gestion', 'sort_order' => 3],
                ['slug' => 'sciences-techniques', 'name' => 'Sciences Techniques', 'sort_order' => 4],
                ['slug' => 'sciences-informatiques', 'name' => 'Sciences de l\'Informatique', 'sort_order' => 5],
                ['slug' => 'lettres', 'name' => 'Lettres', 'sort_order' => 6],
            ],
        ];

        foreach ($data as $levelSlug => $branches) {
            $level = $levels->get($levelSlug);
            if (!$level) continue;

            foreach ($branches as $b) {
                Branch::updateOrCreate(
                    [
                        'level_id' => $level->id,
                        'slug' => $b['slug'],
                    ],
                    [
                        'track_id' => $tunisia->id,
                        'name' => $b['name'],
                        'name_ar' => null,
                        'is_active' => true,
                        'sort_order' => $b['sort_order'] ?? 0,
                    ]
                );
            }
        }
    }
}