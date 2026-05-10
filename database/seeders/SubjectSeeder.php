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
    }
}