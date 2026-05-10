<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Track;
use App\Models\Level;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
        $tracks = Track::all()->keyBy('slug');

        // Exemple Tunisie (primary + middle + lycee)
        $data = [
            'tunisia' => [
                // Primaire 1-6
                ['slug'=>'primary-1','name'=>'1ère année','sort_order'=>1],
                ['slug'=>'primary-2','name'=>'2ème année','sort_order'=>2],
                ['slug'=>'primary-3','name'=>'3ème année','sort_order'=>3],
                ['slug'=>'primary-4','name'=>'4ème année','sort_order'=>4],
                ['slug'=>'primary-5','name'=>'5ème année','sort_order'=>5],
                ['slug'=>'primary-6','name'=>'6ème année','sort_order'=>6],
                // Collège 7-9
                ['slug'=>'middle-7','name'=>'7ème année','sort_order'=>7],
                ['slug'=>'middle-8','name'=>'8ème année','sort_order'=>8],
                ['slug'=>'middle-9','name'=>'9ème année','sort_order'=>9],
                // Lycée 10-12 (exemple)
                ['slug'=>'lycee-10','name'=>'1ère année secondaire','sort_order'=>10],
                ['slug'=>'lycee-11','name'=>'2ème année secondaire','sort_order'=>11],
                ['slug'=>'lycee-12','name'=>'3ème année secondaire','sort_order'=>12],
                ['slug'=>'baccalaureat','name'=>'Baccalauréat','sort_order'=>13],
            ],
            // Qatar: primaire 1-6 / collège 7-9 / lycée 10-13
            'qatar' => [
                ['slug'=>'primary-1','name'=>'1ère année','sort_order'=>1],
                ['slug'=>'primary-2','name'=>'2ème année','sort_order'=>2],
                ['slug'=>'primary-3','name'=>'3ème année','sort_order'=>3],
                ['slug'=>'primary-4','name'=>'4ème année','sort_order'=>4],
                ['slug'=>'primary-5','name'=>'5ème année','sort_order'=>5],
                ['slug'=>'primary-6','name'=>'6ème année','sort_order'=>6],
                ['slug'=>'middle-7','name'=>'7ème année','sort_order'=>7],
                ['slug'=>'middle-8','name'=>'8ème année','sort_order'=>8],
                ['slug'=>'middle-9','name'=>'9ème année','sort_order'=>9],
                ['slug'=>'lycee-10','name'=>'10ème année','sort_order'=>10],
                ['slug'=>'lycee-11','name'=>'11ème année','sort_order'=>11],
                ['slug'=>'lycee-12','name'=>'12ème année','sort_order'=>12],
                ['slug'=>'lycee-13','name'=>'13ème année','sort_order'=>13],
            ],
            // Arabie Saoudite: primaire 1-6 / collège 1-3 / lycée 1-3
            'saudi' => [
                ['slug'=>'primary-1','name'=>'1ère année primaire','sort_order'=>1],
                ['slug'=>'primary-2','name'=>'2ème année primaire','sort_order'=>2],
                ['slug'=>'primary-3','name'=>'3ème année primaire','sort_order'=>3],
                ['slug'=>'primary-4','name'=>'4ème année primaire','sort_order'=>4],
                ['slug'=>'primary-5','name'=>'5ème année primaire','sort_order'=>5],
                ['slug'=>'primary-6','name'=>'6ème année primaire','sort_order'=>6],
                ['slug'=>'college-1','name'=>'1ère année collège','sort_order'=>7],
                ['slug'=>'college-2','name'=>'2ème année collège','sort_order'=>8],
                ['slug'=>'college-3','name'=>'3ème année collège','sort_order'=>9],
                ['slug'=>'lycee-1','name'=>'1ère année lycée','sort_order'=>10],
                ['slug'=>'lycee-2','name'=>'2ème année lycée','sort_order'=>11],
                ['slug'=>'lycee-3','name'=>'3ème année lycée','sort_order'=>12],
            ],
            'quran' => [
                ['slug'=>'quran-debutant',    'name'=>'Debutant (Iqra)',       'sort_order'=>1],
                ['slug'=>'quran-lecture',     'name'=>'Lecture courante',      'sort_order'=>2],
                ['slug'=>'quran-tajweed',     'name'=>'Tajweed',               'sort_order'=>3],
                ['slug'=>'quran-memorisation','name'=>'Memorisation (Hifz)',   'sort_order'=>4],
                ['slug'=>'quran-tafsir',      'name'=>'Tafsir & Sciences',     'sort_order'=>5],
            ],
        ];

        foreach ($data as $trackSlug => $levels) {
            $track = $tracks->get($trackSlug);
            if (!$track) continue;

            foreach ($levels as $l) {
                Level::updateOrCreate(
                    ['track_id' => $track->id, 'slug' => $l['slug']],
                    [
                        'name' => $l['name'],
                        'name_ar' => null,
                        'is_active' => true,
                        'sort_order' => $l['sort_order'] ?? 0,
                    ]
                );
            }
        }
    }
}