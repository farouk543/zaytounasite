<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use Illuminate\Database\Seeder;

/**
 * Catégories de départ du blog, pensées autour de ce que les parents cherchent sur Google.
 * Idempotent : peut être relancé sans doublon.
 */
class BlogCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['revisions', 'Méthodes & révisions', 'طرق المراجعة', 'Study methods & revision',
                'Méthodes de travail, organisation et techniques de mémorisation pour réussir à l’école.'],
            ['examens', 'Examens & bac', 'الامتحانات والباكالوريا', 'Exams & baccalaureate',
                'Préparer le bac, le brevet, le concours de la 6e et les examens nationaux sereinement.'],
            ['coran-arabe', 'Coran & langue arabe', 'القرآن واللغة العربية', 'Quran & Arabic',
                'Apprendre le Coran, le tajwid et la langue arabe, à tout âge.'],
            ['parents', 'Conseils aux parents', 'نصائح للأولياء', 'Tips for parents',
                'Accompagner son enfant dans sa scolarité, à la maison comme à distance.'],
            ['orientation', 'Orientation', 'التوجيه', 'Guidance',
                'Choisir sa filière, sa section et préparer son avenir.'],
            ['actualites', 'Actualités Zaytouna', 'أخبار الزيتونة', 'Zaytouna news',
                'Nouveaux cours, programmes, clubs et événements de Zaytouna Academy.'],
        ];

        foreach ($categories as $i => [$slug, $fr, $ar, $en, $desc]) {
            BlogCategory::firstOrCreate(['slug' => $slug], [
                'name' => $fr, 'name_ar' => $ar, 'name_en' => $en,
                'description' => $desc, 'sort_order' => $i, 'is_active' => true,
            ]);
        }
    }
}
