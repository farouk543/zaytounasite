<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Track;
use App\Models\Level;
use App\Models\Branch;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $tunisia = Track::where('slug', 'tunisia')->first();
        if (!$tunisia) return;

        $levels = Level::where('track_id', $tunisia->id)->get()->keyBy('slug');

        $data = [

            // =========================
            // 2ème année secondaire
            // =========================
            'lycee-11' => [

                'sciences' => [
                    ['slug' => 'arabic', 'name' => 'Arabe', 'sort_order' => 1],
                    ['slug' => 'french', 'name' => 'Français', 'sort_order' => 2],
                    ['slug' => 'english', 'name' => 'Anglais', 'sort_order' => 3],
                    ['slug' => 'history-geo', 'name' => 'Histoire & Géographie', 'sort_order' => 4],
                    ['slug' => 'islamic-education', 'name' => 'Éducation Islamique', 'sort_order' => 5],
                    ['slug' => 'informatics', 'name' => 'Informatique', 'sort_order' => 6],
                    ['slug' => 'math', 'name' => 'Mathématiques', 'sort_order' => 7],
                    ['slug' => 'physics', 'name' => 'Sciences Physiques', 'sort_order' => 8],
                    ['slug' => 'svt', 'name' => 'Sciences de la Vie et de la Terre', 'sort_order' => 9],
                ],

                'lettres' => [
                    ['slug' => 'arabic', 'name' => 'Arabe', 'sort_order' => 1],
                    ['slug' => 'french', 'name' => 'Français', 'sort_order' => 2],
                    ['slug' => 'english', 'name' => 'Anglais', 'sort_order' => 3],
                    ['slug' => 'history-geo', 'name' => 'Histoire & Géographie', 'sort_order' => 4],
                    ['slug' => 'islamic-thought', 'name' => 'Pensée Islamique', 'sort_order' => 5],
                    ['slug' => 'math-light', 'name' => 'Mathématiques', 'sort_order' => 6],
                    ['slug' => 'svt', 'name' => 'Sciences de la Vie et de la Terre', 'sort_order' => 7],
                    ['slug' => 'informatics', 'name' => 'Informatique', 'sort_order' => 8],
                ],

                'eco-services' => [
                    ['slug' => 'economics', 'name' => 'Économie', 'sort_order' => 1],
                    ['slug' => 'management', 'name' => 'Gestion', 'sort_order' => 2],
                    ['slug' => 'math', 'name' => 'Mathématiques', 'sort_order' => 3],
                    ['slug' => 'history-geo', 'name' => 'Histoire & Géographie', 'sort_order' => 4],
                    ['slug' => 'arabic', 'name' => 'Arabe', 'sort_order' => 5],
                    ['slug' => 'french', 'name' => 'Français', 'sort_order' => 6],
                    ['slug' => 'english', 'name' => 'Anglais', 'sort_order' => 7],
                    ['slug' => 'svt', 'name' => 'Sciences de la Vie et de la Terre', 'sort_order' => 8],
                    ['slug' => 'informatics', 'name' => 'Informatique', 'sort_order' => 9],
                    ['slug' => 'islamic-education', 'name' => 'Éducation Islamique', 'sort_order' => 10],
                ],

                'informatique' => [
                    ['slug' => 'programming', 'name' => 'Algorithmique & Programmation', 'sort_order' => 1],
                    ['slug' => 'ict', 'name' => 'TIC', 'sort_order' => 2],
                    ['slug' => 'math', 'name' => 'Mathématiques', 'sort_order' => 3],
                    ['slug' => 'physics', 'name' => 'Sciences Physiques', 'sort_order' => 4],
                    ['slug' => 'arabic', 'name' => 'Arabe', 'sort_order' => 5],
                    ['slug' => 'french', 'name' => 'Français', 'sort_order' => 6],
                    ['slug' => 'english', 'name' => 'Anglais', 'sort_order' => 7],
                    ['slug' => 'history-geo', 'name' => 'Histoire & Géographie', 'sort_order' => 8],
                    ['slug' => 'islamic-education', 'name' => 'Éducation Islamique', 'sort_order' => 9],
                ],
            ],

            // =========================
            // 3ème année secondaire
            // =========================
            'lycee-12' => [

                'math' => [
                    ['slug' => 'math', 'name' => 'Mathématiques', 'sort_order' => 1],
                    ['slug' => 'physics', 'name' => 'Sciences Physiques', 'sort_order' => 2],
                    ['slug' => 'svt', 'name' => 'Sciences de la Vie et de la Terre', 'sort_order' => 3],
                    ['slug' => 'informatics', 'name' => 'Informatique', 'sort_order' => 4],
                    ['slug' => 'french', 'name' => 'Français', 'sort_order' => 5],
                    ['slug' => 'arabic', 'name' => 'Arabe', 'sort_order' => 6],
                    ['slug' => 'english', 'name' => 'Anglais', 'sort_order' => 7],
                    ['slug' => 'philosophy', 'name' => 'Philosophie', 'sort_order' => 8],
                ],

                'sciences-experimentales' => [
                    ['slug' => 'svt', 'name' => 'Sciences de la Vie et de la Terre', 'sort_order' => 1],
                    ['slug' => 'physics', 'name' => 'Sciences Physiques', 'sort_order' => 2],
                    ['slug' => 'math', 'name' => 'Mathématiques', 'sort_order' => 3],
                    ['slug' => 'french', 'name' => 'Français', 'sort_order' => 4],
                    ['slug' => 'english', 'name' => 'Anglais', 'sort_order' => 5],
                    ['slug' => 'arabic', 'name' => 'Arabe', 'sort_order' => 6],
                    ['slug' => 'philosophy', 'name' => 'Philosophie', 'sort_order' => 7],
                ],

                'eco-gestion' => [
                    ['slug' => 'economics', 'name' => 'Économie', 'sort_order' => 1],
                    ['slug' => 'management', 'name' => 'Gestion', 'sort_order' => 2],
                    ['slug' => 'applied-math', 'name' => 'Mathématiques appliquées', 'sort_order' => 3],
                    ['slug' => 'history-geo', 'name' => 'Histoire-Géographie', 'sort_order' => 4],
                    ['slug' => 'french', 'name' => 'Français', 'sort_order' => 5],
                    ['slug' => 'english', 'name' => 'Anglais', 'sort_order' => 6],
                    ['slug' => 'arabic', 'name' => 'Arabe', 'sort_order' => 7],
                    ['slug' => 'philosophy', 'name' => 'Philosophie', 'sort_order' => 8],
                    ['slug' => 'informatics', 'name' => 'Informatique', 'sort_order' => 9],
                ],

                'sciences-techniques' => [
                    ['slug' => 'technology', 'name' => 'Technologie', 'sort_order' => 1],
                    ['slug' => 'physics', 'name' => 'Sciences Physiques', 'sort_order' => 2],
                    ['slug' => 'math', 'name' => 'Mathématiques', 'sort_order' => 3],
                    ['slug' => 'french', 'name' => 'Français', 'sort_order' => 4],
                    ['slug' => 'english', 'name' => 'Anglais', 'sort_order' => 5],
                    ['slug' => 'arabic', 'name' => 'Arabe', 'sort_order' => 6],
                    ['slug' => 'philosophy', 'name' => 'Philosophie', 'sort_order' => 7],
                ],

                'sciences-informatiques' => [
                    ['slug' => 'programming', 'name' => 'Algorithmique et Programmation', 'sort_order' => 1],
                    ['slug' => 'databases', 'name' => 'Bases de Données', 'sort_order' => 2],
                    ['slug' => 'ict', 'name' => 'TIC', 'sort_order' => 3],
                    ['slug' => 'math', 'name' => 'Mathématiques', 'sort_order' => 4],
                    ['slug' => 'physics', 'name' => 'Sciences Physiques', 'sort_order' => 5],
                    ['slug' => 'french', 'name' => 'Français', 'sort_order' => 6],
                    ['slug' => 'english', 'name' => 'Anglais', 'sort_order' => 7],
                    ['slug' => 'arabic', 'name' => 'Arabe', 'sort_order' => 8],
                    ['slug' => 'philosophy', 'name' => 'Philosophie', 'sort_order' => 9],
                ],

                'lettres' => [
                    ['slug' => 'arabic', 'name' => 'Arabe', 'sort_order' => 1],
                    ['slug' => 'french', 'name' => 'Français', 'sort_order' => 2],
                    ['slug' => 'english', 'name' => 'Anglais', 'sort_order' => 3],
                    ['slug' => 'history-geo', 'name' => 'Histoire-Géographie', 'sort_order' => 4],
                    ['slug' => 'philosophy', 'name' => 'Philosophie', 'sort_order' => 5],
                    ['slug' => 'islamic-thought', 'name' => 'Pensée Islamique', 'sort_order' => 6],
                    ['slug' => 'math-light', 'name' => 'Mathématiques', 'sort_order' => 7],
                    ['slug' => 'informatics', 'name' => 'Informatique', 'sort_order' => 8],
                ],
            ],

            // =========================
            // Bac
            // =========================
            'baccalaureat' => [

                'math' => [
                    ['slug' => 'math', 'name' => 'Mathématiques', 'sort_order' => 1],
                    ['slug' => 'physics', 'name' => 'Sciences Physiques', 'sort_order' => 2],
                    ['slug' => 'svt', 'name' => 'Sciences de la Vie et de la Terre', 'sort_order' => 3],
                    ['slug' => 'french', 'name' => 'Français', 'sort_order' => 4],
                    ['slug' => 'english', 'name' => 'Anglais', 'sort_order' => 5],
                    ['slug' => 'arabic', 'name' => 'Arabe', 'sort_order' => 6],
                    ['slug' => 'philosophy', 'name' => 'Philosophie', 'sort_order' => 7],
                ],

                'sciences-experimentales' => [
                    ['slug' => 'svt', 'name' => 'Sciences de la Vie et de la Terre', 'sort_order' => 1],
                    ['slug' => 'physics', 'name' => 'Sciences Physiques', 'sort_order' => 2],
                    ['slug' => 'math', 'name' => 'Mathématiques', 'sort_order' => 3],
                    ['slug' => 'french', 'name' => 'Français', 'sort_order' => 4],
                    ['slug' => 'english', 'name' => 'Anglais', 'sort_order' => 5],
                    ['slug' => 'arabic', 'name' => 'Arabe', 'sort_order' => 6],
                    ['slug' => 'philosophy', 'name' => 'Philosophie', 'sort_order' => 7],
                ],

                'eco-gestion' => [
                    ['slug' => 'economics', 'name' => 'Économie', 'sort_order' => 1],
                    ['slug' => 'management', 'name' => 'Gestion', 'sort_order' => 2],
                    ['slug' => 'math', 'name' => 'Mathématiques', 'sort_order' => 3],
                    ['slug' => 'history-geo', 'name' => 'Histoire-Géographie', 'sort_order' => 4],
                    ['slug' => 'french', 'name' => 'Français', 'sort_order' => 5],
                    ['slug' => 'english', 'name' => 'Anglais', 'sort_order' => 6],
                    ['slug' => 'arabic', 'name' => 'Arabe', 'sort_order' => 7],
                    ['slug' => 'philosophy', 'name' => 'Philosophie', 'sort_order' => 8],
                    ['slug' => 'informatics', 'name' => 'Informatique', 'sort_order' => 9],
                ],

                'sciences-techniques' => [
                    ['slug' => 'technology', 'name' => 'Technologie', 'sort_order' => 1],
                    ['slug' => 'physics', 'name' => 'Sciences Physiques', 'sort_order' => 2],
                    ['slug' => 'math', 'name' => 'Mathématiques', 'sort_order' => 3],
                    ['slug' => 'french', 'name' => 'Français', 'sort_order' => 4],
                    ['slug' => 'english', 'name' => 'Anglais', 'sort_order' => 5],
                    ['slug' => 'arabic', 'name' => 'Arabe', 'sort_order' => 6],
                    ['slug' => 'philosophy', 'name' => 'Philosophie', 'sort_order' => 7],
                ],

                'sciences-informatiques' => [
                    ['slug' => 'programming', 'name' => 'Algorithmique et Programmation', 'sort_order' => 1],
                    ['slug' => 'systems-technologies', 'name' => 'Systèmes et Technologies', 'sort_order' => 2],
                    ['slug' => 'math', 'name' => 'Mathématiques', 'sort_order' => 3],
                    ['slug' => 'physics', 'name' => 'Sciences Physiques', 'sort_order' => 4],
                    ['slug' => 'french', 'name' => 'Français', 'sort_order' => 5],
                    ['slug' => 'english', 'name' => 'Anglais', 'sort_order' => 6],
                    ['slug' => 'arabic', 'name' => 'Arabe', 'sort_order' => 7],
                    ['slug' => 'philosophy', 'name' => 'Philosophie', 'sort_order' => 8],
                ],

                'lettres' => [
                    ['slug' => 'arabic', 'name' => 'Arabe', 'sort_order' => 1],
                    ['slug' => 'french', 'name' => 'Français', 'sort_order' => 2],
                    ['slug' => 'english', 'name' => 'Anglais', 'sort_order' => 3],
                    ['slug' => 'history-geo', 'name' => 'Histoire-Géographie', 'sort_order' => 4],
                    ['slug' => 'philosophy', 'name' => 'Philosophie', 'sort_order' => 5],
                    ['slug' => 'islamic-thought', 'name' => 'Pensée Islamique', 'sort_order' => 6],
                    ['slug' => 'math-light', 'name' => 'Mathématiques', 'sort_order' => 7],
                    ['slug' => 'informatics', 'name' => 'Informatique', 'sort_order' => 8],
                ],
            ],
        ];

        foreach ($data as $levelSlug => $branchSubjects) {
            $level = $levels->get($levelSlug);
            if (!$level) continue;

            foreach ($branchSubjects as $branchSlug => $subjects) {
                $branch = Branch::where('level_id', $level->id)
                    ->where('slug', $branchSlug)
                    ->first();

                if (!$branch) continue;

                foreach ($subjects as $s) {
                    Subject::updateOrCreate(
                        [
                            'branch_id' => $branch->id,
                            'slug' => $s['slug'],
                        ],
                        [
                            'level_id' => $level->id,
                            'name' => $s['name'],
                            'name_ar' => null,
                            'is_active' => true,
                            'sort_order' => $s['sort_order'] ?? 0,
                        ]
                    );
                }
            }
        }

        $this->seedFrance();
    }

    /**
     * Régime France : système éducatif français, sans séries/branches
     * (réforme du lycée 2019). Matières par niveau, branch_id = null.
     * Sources : education.gouv.fr / éduscol / Onisep.
     */
    private function seedFrance(): void
    {
        $france = Track::where('slug', 'france')->first();
        if (! $france) return;

        $levels = Level::where('track_id', $france->id)->get()->keyBy('slug');

        // Enseignements de spécialité (Première, Terminale, Bac)
        $specialites = [
            ['slug' => 'spe-mathematiques',   'name' => 'Spécialité Mathématiques'],
            ['slug' => 'spe-physique-chimie', 'name' => 'Spécialité Physique-Chimie'],
            ['slug' => 'spe-svt',             'name' => 'Spécialité Sciences de la vie et de la Terre'],
            ['slug' => 'spe-ses',             'name' => 'Spécialité Sciences économiques et sociales'],
            ['slug' => 'spe-hlp',             'name' => 'Spécialité Humanités, littérature et philosophie'],
            ['slug' => 'spe-hggsp',           'name' => 'Spécialité Histoire-géo, géopolitique et sciences politiques'],
            ['slug' => 'spe-nsi',             'name' => 'Spécialité Numérique et sciences informatiques'],
            ['slug' => 'spe-llcer',           'name' => 'Spécialité LLCER (Anglais)'],
            ['slug' => 'spe-si',              'name' => 'Spécialité Sciences de l\'ingénieur'],
            ['slug' => 'spe-arts',            'name' => 'Spécialité Arts'],
        ];

        $primaire = [
            ['slug' => 'francais',              'name' => 'Français'],
            ['slug' => 'mathematiques',         'name' => 'Mathématiques'],
            ['slug' => 'sciences-technologie',  'name' => 'Sciences et technologie'],
            ['slug' => 'histoire-geographie',   'name' => 'Histoire-Géographie'],
            ['slug' => 'emc',                   'name' => 'Enseignement moral et civique'],
            ['slug' => 'anglais',               'name' => 'Anglais'],
            ['slug' => 'arts-plastiques',       'name' => 'Arts plastiques'],
            ['slug' => 'education-musicale',    'name' => 'Éducation musicale'],
            ['slug' => 'eps',                   'name' => 'Éducation physique et sportive'],
        ];

        $sixieme = [
            ['slug' => 'francais',                'name' => 'Français'],
            ['slug' => 'mathematiques',           'name' => 'Mathématiques'],
            ['slug' => 'histoire-geographie-emc', 'name' => 'Histoire-Géographie-EMC'],
            ['slug' => 'anglais-lv1',             'name' => 'Anglais (LV1)'],
            ['slug' => 'sciences-technologie',    'name' => 'Sciences et technologie'],
            ['slug' => 'arts-plastiques',         'name' => 'Arts plastiques'],
            ['slug' => 'education-musicale',      'name' => 'Éducation musicale'],
            ['slug' => 'eps',                     'name' => 'Éducation physique et sportive'],
        ];

        $cycle4 = [
            ['slug' => 'francais',                'name' => 'Français'],
            ['slug' => 'mathematiques',           'name' => 'Mathématiques'],
            ['slug' => 'histoire-geographie-emc', 'name' => 'Histoire-Géographie-EMC'],
            ['slug' => 'anglais-lv1',             'name' => 'Anglais (LV1)'],
            ['slug' => 'lv2',                     'name' => 'Langue vivante 2'],
            ['slug' => 'svt',                     'name' => 'Sciences de la vie et de la Terre'],
            ['slug' => 'physique-chimie',         'name' => 'Physique-Chimie'],
            ['slug' => 'technologie',             'name' => 'Technologie'],
            ['slug' => 'arts-plastiques',         'name' => 'Arts plastiques'],
            ['slug' => 'education-musicale',      'name' => 'Éducation musicale'],
            ['slug' => 'eps',                     'name' => 'Éducation physique et sportive'],
        ];

        $seconde = [
            ['slug' => 'francais',           'name' => 'Français'],
            ['slug' => 'mathematiques',      'name' => 'Mathématiques'],
            ['slug' => 'histoire-geographie', 'name' => 'Histoire-Géographie'],
            ['slug' => 'lva-anglais',        'name' => 'LVA (Anglais)'],
            ['slug' => 'lvb',                'name' => 'LVB'],
            ['slug' => 'ses',                'name' => 'Sciences économiques et sociales'],
            ['slug' => 'svt',                'name' => 'Sciences de la vie et de la Terre'],
            ['slug' => 'physique-chimie',    'name' => 'Physique-Chimie'],
            ['slug' => 'snt',                'name' => 'Sciences numériques et technologie'],
            ['slug' => 'eps',                'name' => 'Éducation physique et sportive'],
            ['slug' => 'emc',                'name' => 'Enseignement moral et civique'],
        ];

        $premiereTC = [
            ['slug' => 'francais',                  'name' => 'Français'],
            ['slug' => 'histoire-geographie',       'name' => 'Histoire-Géographie'],
            ['slug' => 'lva-anglais',               'name' => 'LVA (Anglais)'],
            ['slug' => 'lvb',                       'name' => 'LVB'],
            ['slug' => 'enseignement-scientifique', 'name' => 'Enseignement scientifique'],
            ['slug' => 'eps',                       'name' => 'Éducation physique et sportive'],
            ['slug' => 'emc',                       'name' => 'Enseignement moral et civique'],
        ];

        $terminaleTC = [
            ['slug' => 'philosophie',               'name' => 'Philosophie'],
            ['slug' => 'histoire-geographie',       'name' => 'Histoire-Géographie'],
            ['slug' => 'lva-anglais',               'name' => 'LVA (Anglais)'],
            ['slug' => 'lvb',                       'name' => 'LVB'],
            ['slug' => 'enseignement-scientifique', 'name' => 'Enseignement scientifique'],
            ['slug' => 'eps',                       'name' => 'Éducation physique et sportive'],
            ['slug' => 'emc',                       'name' => 'Enseignement moral et civique'],
        ];

        $bac = array_merge([
            ['slug' => 'francais',    'name' => 'Français (épreuve anticipée)'],
            ['slug' => 'philosophie', 'name' => 'Philosophie'],
            ['slug' => 'grand-oral',  'name' => 'Grand oral'],
        ], $specialites);

        $data = [
            'primary-1' => $primaire,
            'primary-2' => $primaire,
            'primary-3' => $primaire,
            'primary-4' => $primaire,
            'primary-5' => $primaire,
            'middle-6'  => $sixieme,
            'middle-7'  => $cycle4,
            'middle-8'  => $cycle4,
            'middle-9'  => $cycle4,
            'lycee-10'  => $seconde,
            'lycee-11'  => array_merge($premiereTC, $specialites),
            'lycee-12'  => array_merge($terminaleTC, $specialites),
            'baccalaureat' => $bac,
        ];

        foreach ($data as $levelSlug => $subjects) {
            $level = $levels->get($levelSlug);
            if (! $level) continue;

            $order = 1;
            foreach ($subjects as $s) {
                Subject::updateOrCreate(
                    [
                        'level_id'  => $level->id,
                        'branch_id' => null,
                        'slug'      => $s['slug'],
                    ],
                    [
                        'name'       => $s['name'],
                        'name_ar'    => null,
                        'is_active'  => true,
                        'sort_order' => $order++,
                    ]
                );
            }
        }
    }
}