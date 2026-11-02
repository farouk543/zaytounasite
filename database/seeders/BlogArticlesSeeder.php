<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Les 11 premiers articles du blog, publiés automatiquement selon un calendrier éditorial,
 * ciblant nos 5 marchés prioritaires (Tunisie, France, Arabie saoudite, Qatar, Canada).
 *
 * - Contenu : database/seeders/content/blog/<slug>/{fr,ar,en}.html (une langue absente = non traduite).
 * - Couvertures : database/seeders/assets/blog/<slug>.jpg, copiées sur le disque public.
 * - Idempotent : un article déjà présent (même adresse) n'est JAMAIS modifié, pour ne pas écraser
 *   les retouches faites dans le back-office. Relancer le seeder ne crée que les articles manquants.
 * - Date future = publication programmée : l'article apparaît tout seul sur le site à 8 h (heure de Tunis).
 */
class BlogArticlesSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(BlogCategorySeeder::class);

        $created = 0;

        foreach ($this->articles() as $slug => $a) {
            if (BlogPost::where('slug', $slug)->exists()) {
                $this->command?->line("  • {$slug} : déjà présent, inchangé");

                continue;
            }

            $post = new BlogPost([
                'slug' => $slug,
                'blog_category_id' => BlogCategory::where('slug', $a['category'])->value('id'),
                'focus_keyword' => $a['keyword'],
                'target_countries' => $a['countries'],
                'is_featured' => $a['featured'] ?? false,
                'status' => 'published',
                'published_at' => CarbonImmutable::parse($a['date'].' 08:00', 'Africa/Tunis')->utc(),
                'cover_image' => $this->copyCover($slug),
            ]);

            foreach (['fr' => '', 'ar' => '_ar', 'en' => '_en'] as $locale => $suffix) {
                $content = $this->content($slug, $locale);
                $meta = $a[$locale] ?? null;

                if (! $content || ! $meta) {
                    continue;
                }

                $post->{'title'.$suffix} = $meta['title'];
                $post->{'excerpt'.$suffix} = $meta['excerpt'];
                $post->{'meta_title'.$suffix} = $meta['meta_title'];
                $post->{'meta_description'.$suffix} = $meta['meta_description'];
                $post->{'content'.$suffix} = $content;
            }

            $post->save();
            $created++;
            $this->command?->info("  ✓ {$slug} — publication le {$a['date']}");
        }

        $this->command?->info("Blog : {$created} article(s) créé(s).");
    }

    private function content(string $slug, string $locale): ?string
    {
        $path = database_path("seeders/content/blog/{$slug}/{$locale}.html");

        return is_file($path) ? trim(file_get_contents($path)) : null;
    }

    private function copyCover(string $slug): ?string
    {
        $source = database_path("seeders/assets/blog/{$slug}.jpg");
        $target = "blog/covers/{$slug}.jpg";

        if (! is_file($source)) {
            return null;
        }

        if (! Storage::disk('public')->exists($target)) {
            Storage::disk('public')->put($target, file_get_contents($source));
        }

        return $target;
    }

    /**
     * Calendrier éditorial : une publication par semaine (mercredi), sauf la dernière
     * calée début janvier pour préparer Ramadan.
     */
    private function articles(): array
    {
        return [
            'reviser-bac-tunisie-2027' => [
                'countries' => ['TN'],
                'date' => '2026-11-04', 'category' => 'examens', 'featured' => true,
                'keyword' => 'réviser bac Tunisie 2027',
                'fr' => [
                    'title' => 'Bac 2027 en Tunisie : le planning de révision mois par mois',
                    'excerpt' => 'De novembre à juin, le calendrier de révision recommandé par nos enseignants pour aborder le bac 2027 sereinement : priorités, méthode et gestion du stress.',
                    'meta_title' => 'Bac 2027 Tunisie : planning de révision mois par mois',
                    'meta_description' => 'Réviser le bac 2027 en Tunisie sans stress : le planning mois par mois de nos enseignants, les matières à prioriser et la méthode fiches + annales.',
                ],
                'ar' => [
                    'title' => 'باكالوريا 2027 في تونس: برنامج المراجعة شهرًا بشهر',
                    'excerpt' => 'من نوفمبر إلى جوان، رزنامة المراجعة التي ينصح بها أساتذتنا لاجتياز باكالوريا 2027 بهدوء: الأولويات والمنهجية والتعامل مع التوتّر.',
                    'meta_title' => 'باكالوريا 2027 تونس: برنامج المراجعة شهرًا بشهر',
                    'meta_description' => 'كيف تراجع لباكالوريا 2027 في تونس دون توتّر: رزنامة شهرية، المواد ذات الأولوية، وطريقة الملخّصات والمواضيع السابقة.',
                ],
            ],

            'concours-6eme-9eme-tunisie' => [
                'countries' => ['TN'],
                'date' => '2026-11-11', 'category' => 'examens',
                'keyword' => 'concours 6ème Tunisie',
                'fr' => [
                    'title' => 'Concours de la 6e et de la 9e : bien se préparer aux collèges et lycées pilotes',
                    'excerpt' => 'Comprendre les concours d’entrée aux établissements pilotes en Tunisie, les compétences évaluées et un plan de préparation sur six mois, sans épuiser son enfant.',
                    'meta_title' => 'Concours 6e et 9e Tunisie : réussir l’entrée en pilote',
                    'meta_description' => 'Préparer le concours de la 6e ou de la 9e en Tunisie : plan sur 6 mois, entraînement sur les sujets passés et erreurs à éviter pour entrer en pilote.',
                ],
                'ar' => [
                    'title' => 'مناظرة السادسة والتاسعة أساسي: كيف تستعدّ للإعداديات والمعاهد النموذجية',
                    'excerpt' => 'فهم مناظرات الدخول إلى المؤسسات النموذجية في تونس، والمهارات المطلوبة، وخطة تحضير على ستة أشهر دون إرهاق الطفل.',
                    'meta_title' => 'مناظرة السادسة والتاسعة: النجاح في دخول النموذجي',
                    'meta_description' => 'التحضير لمناظرة السادسة أو التاسعة أساسي في تونس: خطة على ستة أشهر، التمرّن على المواضيع السابقة والأخطاء التي يجب تجنّبها.',
                ],
            ],

            'enfant-difficultes-maths' => [
                'countries' => ['TN', 'FR', 'SA', 'QA', 'CA'],
                'date' => '2026-11-18', 'category' => 'parents',
                'keyword' => 'enfant difficulté maths',
                'fr' => [
                    'title' => 'Mon enfant a des difficultés en maths : 7 signes et comment l’aider',
                    'excerpt' => 'Notes en baisse, devoirs interminables, « je suis nul en maths »… Les signes à repérer, l’origine des blocages et ce que vous pouvez faire à la maison, du primaire au lycée.',
                    'meta_title' => 'Enfant en difficulté en maths : 7 signes et solutions',
                    'meta_description' => 'Votre enfant décroche en maths ? Les 7 signes d’alerte, l’origine des blocages et des solutions concrètes à la maison, du primaire au lycée.',
                ],
                'ar' => [
                    'title' => 'طفلي يجد صعوبة في الرياضيات: سبع علامات وكيف أساعده',
                    'excerpt' => 'أعداد متراجعة وواجبات لا تنتهي و«أنا لا أفهم الرياضيات»… العلامات التي يجب الانتباه إليها وأصل الصعوبات وما يمكنك فعله في البيت.',
                    'meta_title' => 'صعوبات الرياضيات عند الطفل: 7 علامات وحلول',
                    'meta_description' => 'هل يتراجع طفلك في الرياضيات؟ سبع علامات إنذار، أصل الصعوبات وحلول عملية في المنزل من الابتدائي إلى الثانوي.',
                ],
            ],

            'cours-particuliers-en-ligne-ou-domicile' => [
                'countries' => ['TN', 'FR', 'SA', 'QA', 'CA'],
                'date' => '2026-11-25', 'category' => 'parents',
                'keyword' => 'cours particuliers en ligne',
                'fr' => [
                    'title' => 'Cours particuliers en ligne ou à domicile : lequel choisir ?',
                    'excerpt' => 'Choix de l’enseignant, prix, efficacité, âge de l’enfant : un comparatif honnête entre les cours à domicile et les cours particuliers en ligne en direct.',
                    'meta_title' => 'Cours particuliers en ligne ou à domicile : que choisir ?',
                    'meta_description' => 'Cours particuliers en ligne ou à domicile ? Comparatif prix, efficacité et flexibilité, conseils selon l’âge et comment bien préparer un cours en ligne.',
                ],
                'en' => [
                    'title' => 'Online or home tutoring: which one should you choose?',
                    'excerpt' => 'Choice of teacher, price, effectiveness, age of the child: an honest comparison between home tutoring and live online private lessons.',
                    'meta_title' => 'Online vs home tutoring: which one to choose?',
                    'meta_description' => 'Online or home tutoring? Compare price, effectiveness and flexibility, get advice by age and learn how to prepare for a live online lesson.',
                ],
            ],

            'revisions-vacances-hiver' => [
                'countries' => ['TN', 'FR', 'SA', 'QA', 'CA'],
                'date' => '2026-12-02', 'category' => 'revisions',
                'keyword' => 'révisions vacances d’hiver',
                'fr' => [
                    'title' => 'Vacances d’hiver : un programme de révision en 2 semaines, sans gâcher les vacances',
                    'excerpt' => 'Une heure par jour, un plan jour par jour et des priorités selon le niveau : la méthode pour consolider le premier trimestre et revenir en janvier en confiance.',
                    'meta_title' => 'Vacances d’hiver : programme de révision en 2 semaines',
                    'meta_description' => 'Un programme de révision de 2 semaines pour les vacances d’hiver : 1 h par jour, un plan jour par jour et les priorités du primaire au lycée.',
                ],
                'ar' => [
                    'title' => 'عطلة الشتاء: برنامج مراجعة في أسبوعين دون إفساد العطلة',
                    'excerpt' => 'ساعة يوميًا، وخطة يومًا بيوم، وأولويات حسب المستوى: الطريقة لتثبيت الثلاثي الأول والعودة في جانفي بثقة.',
                    'meta_title' => 'عطلة الشتاء: برنامج مراجعة في أسبوعين',
                    'meta_description' => 'برنامج مراجعة لعطلة الشتاء في أسبوعين: ساعة يوميًا، خطة يومًا بيوم، والأولويات من الابتدائي إلى الثانوي.',
                ],
            ],

            'programme-scolaire-expatries-golfe' => [
                'countries' => ['SA', 'QA', 'TN', 'FR'],
                'date' => '2026-12-09', 'category' => 'parents',
                'keyword' => 'programme tunisien à l’étranger',
                'ar' => [
                    'title' => 'كيف يتابع ابنك البرنامج التونسي أو الفرنسي وهو يعيش في قطر أو السعودية؟',
                    'excerpt' => 'حلول عملية للعائلات المغتربة في الخليج للمحافظة على مستوى أبنائها في برنامج بلدها الأصلي، استعدادًا للعودة أو للامتحانات الوطنية.',
                    'meta_title' => 'البرنامج التونسي والفرنسي للمغتربين في قطر والسعودية',
                    'meta_description' => 'تعيش في قطر أو السعودية؟ كيف يحافظ ابنك على مستواه في البرنامج التونسي أو الفرنسي بدروس مباشرة عن بعد تناسب توقيت الخليج.',
                ],
                'fr' => [
                    'title' => 'Expatriés au Qatar ou en Arabie saoudite : garder le programme tunisien ou français',
                    'excerpt' => 'Des solutions concrètes pour les familles expatriées dans le Golfe qui veulent que leurs enfants gardent le niveau dans le programme de leur pays d’origine.',
                    'meta_title' => 'Expatriés au Golfe : suivre le programme tunisien ou français',
                    'meta_description' => 'Vous vivez au Qatar ou en Arabie saoudite ? Comment garder le niveau dans le programme tunisien ou français grâce aux cours en direct en ligne.',
                ],
            ],

            'apprendre-arabe-enfant-en-ligne' => [
                'countries' => ['FR', 'CA'],
                'date' => '2026-12-16', 'category' => 'coran-arabe',
                'keyword' => 'apprendre l’arabe enfant en ligne',
                'fr' => [
                    'title' => 'Apprendre l’arabe à son enfant en France ou au Canada : méthode et conseils',
                    'excerpt' => 'Dès quel âge commencer, par quoi débuter, comment s’organiser et garder la motivation : le guide pour transmettre la langue arabe à ses enfants en France ou au Canada.',
                    'meta_title' => 'Apprendre l’arabe à son enfant en ligne : le guide',
                    'meta_description' => 'Apprendre l’arabe à son enfant en France ou au Canada : âge idéal, lecture et écriture, horaires, motivation, arabe et Coran. Méthode et conseils.',
                ],
                'en' => [
                    'title' => 'Teaching your child Arabic in Canada or abroad: method and tips',
                    'excerpt' => 'When to start, where to begin and how to keep motivation: a practical guide to passing on the Arabic language to children growing up in Europe or North America.',
                    'meta_title' => 'Teach your child Arabic online: the complete guide',
                    'meta_description' => 'How to teach your child Arabic abroad: the best age to start, reading and writing, motivation, Arabic and Quran. A simple method with practical tips.',
                ],
            ],

            'familles-canada-programme-arabe-coran' => [
                'date' => '2026-12-23', 'category' => 'parents',
                'countries' => ['CA'],
                'keyword' => 'cours d’arabe en ligne Montréal',
                'fr' => [
                    'title' => 'Familles au Canada : garder l’arabe, le Coran et le programme du pays d’origine',
                    'excerpt' => 'Depuis Montréal, Toronto ou Ottawa : trois objectifs possibles, les meilleurs créneaux malgré le décalage horaire et une organisation simple pour toute la famille.',
                    'meta_title' => 'Familles au Canada : arabe, Coran et programme d’origine',
                    'meta_description' => 'Vous vivez à Montréal ou Toronto ? Gardez l’arabe, le Coran et le programme tunisien ou français grâce aux cours en direct, aux bons horaires.',
                ],
                'en' => [
                    'title' => 'Families in Canada: keeping Arabic, the Quran and the home curriculum',
                    'excerpt' => 'From Montreal, Toronto or Ottawa: three possible goals, the best time slots despite the time difference and a simple family routine.',
                    'meta_title' => 'Families in Canada: Arabic, Quran and home curriculum',
                    'meta_description' => 'Living in Montreal or Toronto? Keep Arabic, the Quran and the Tunisian or French curriculum alive with live online lessons at the right times.',
                ],
            ],

            'cours-coran-en-ligne-enfant' => [
                'countries' => ['TN', 'FR', 'SA', 'QA', 'CA'],
                'date' => '2027-01-06', 'category' => 'coran-arabe', 'featured' => true,
                'keyword' => 'cours de Coran en ligne enfant',
                'fr' => [
                    'title' => 'Cours de Coran en ligne pour enfants : comment bien choisir avant Ramadan',
                    'excerpt' => 'Critères d’un bon enseignant, lecture, tajwid ou mémorisation, rythme selon l’âge et objectif réaliste pour Ramadan 2027 : notre guide pour les parents.',
                    'meta_title' => 'Cours de Coran en ligne pour enfants : bien choisir',
                    'meta_description' => 'Choisir un cours de Coran en ligne pour son enfant avant Ramadan : critères de l’enseignant, lecture, tajwid, hifz et rythme adapté à l’âge.',
                ],
                'ar' => [
                    'title' => 'تحفيظ القرآن للأطفال عن بعد: كيف تختار الدرس المناسب قبل رمضان',
                    'excerpt' => 'معايير المعلّم الجيد، القراءة أو التجويد أو الحفظ، الإيقاع المناسب للعمر وهدف واقعي لرمضان 2027: دليل للأولياء.',
                    'meta_title' => 'تحفيظ القرآن للأطفال عن بعد: كيف تختار؟',
                    'meta_description' => 'اختيار درس قرآن عن بعد لطفلك قبل رمضان: معايير المعلّم، القراءة والتجويد والحفظ، والإيقاع المناسب لكل عمر.',
                ],
                'en' => [
                    'title' => 'Online Quran classes for kids: how to choose before Ramadan',
                    'excerpt' => 'What makes a good teacher, reading, tajweed or memorisation, the right pace for each age and a realistic goal for Ramadan 2027: our guide for parents.',
                    'meta_title' => 'Online Quran classes for kids: how to choose',
                    'meta_description' => 'Choosing online Quran classes for your child before Ramadan: teacher criteria, reading, tajweed, hifz and the right pace for every age.',
                ],
            ],

            'soutien-scolaire-programme-francais-en-ligne' => [
                'date' => '2027-01-13', 'category' => 'examens',
                'countries' => ['FR', 'CA', 'QA', 'SA'],
                'keyword' => 'soutien scolaire en ligne programme français',
                'fr' => [
                    'title' => 'Soutien scolaire en ligne au programme français : du brevet au bac',
                    'excerpt' => 'Brevet, épreuves anticipées de français, Grand oral : les échéances clés, les matières où un soutien rapporte le plus et comment choisir, en France comme à l’étranger.',
                    'meta_title' => 'Soutien scolaire en ligne programme français : brevet, bac',
                    'meta_description' => 'Brevet, bac de français, Grand oral : comment un soutien scolaire en ligne au programme français aide votre enfant, en France ou à l’étranger.',
                ],
            ],

            'cours-en-ligne-programme-saoudien-qatari' => [
                'date' => '2027-01-20', 'category' => 'parents',
                'countries' => ['SA', 'QA'],
                'keyword' => 'دروس خصوصية أونلاين المنهج السعودي',
                'ar' => [
                    'title' => 'دروس خصوصية أونلاين للمنهج السعودي والقطري: كيف تختار الأستاذ المناسب؟',
                    'excerpt' => 'من الرياض وجدة إلى الدوحة: لماذا الدروس المباشرة عن بعد، المحطات المهمة في كل منهج، ومعايير اختيار الأستاذ وتنظيم المتابعة.',
                    'meta_title' => 'دروس خصوصية أونلاين للمنهج السعودي والقطري',
                    'meta_description' => 'دروس خصوصية مباشرة عن بعد في السعودية وقطر: نافس، القدرات والتحصيلي، الثانوية العامة، ومعايير اختيار الأستاذ المناسب.',
                ],
                'fr' => [
                    'title' => 'Cours en ligne au programme saoudien et qatari : bien choisir son enseignant',
                    'excerpt' => 'De Riyad à Doha : pourquoi les cours en direct, les étapes clés de chaque programme et les critères pour choisir l’enseignant et organiser le suivi.',
                    'meta_title' => 'Cours en ligne programme saoudien et qatari',
                    'meta_description' => 'Soutien scolaire en direct en Arabie saoudite et au Qatar : tests Nafes, Qudurat et Tahsili, fin du secondaire, et comment choisir l’enseignant.',
                ],
            ],
        ];
    }
}
