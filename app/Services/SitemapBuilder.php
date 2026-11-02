<?php

namespace App\Services;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Course;
use App\Models\CoursePackItem;
use App\Support\Hreflang;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

/**
 * Liste des pages publiques indexables du site. Sert au sitemap.xml (Google)
 * et au back-office (« pages les moins visitées »).
 */
class SitemapBuilder
{
    public const LOCALES = ['fr', 'en', 'ar'];

    /**
     * @return Collection<int, array{loc: string, label: string, lastmod: ?string, changefreq: string, priority: string, alternates: array<string, string>}>
     */
    public function entries(): Collection
    {
        $entries = collect();

        $static = [
            ['home', 'Accueil', 'daily', '1.0'],
            ['catalog', 'Catalogue', 'daily', '0.9'],
            ['regimes.index', 'Cours live (régimes)', 'weekly', '0.9'],
            ['regimes.tunisia.show', 'Régime Tunisie', 'weekly', '0.8'],
            ['regimes.france.show', 'Régime France', 'weekly', '0.8'],
            ['regimes.qatar.show', 'Régime Qatar', 'weekly', '0.8'],
            ['regimes.saudi.show', 'Régime Arabie saoudite', 'weekly', '0.8'],
            ['regimes.quran.show', 'Programme Coran', 'weekly', '0.8'],
            ['blog.index', 'Blog', 'daily', '0.8'],
            ['club.ete', "Club d'été", 'monthly', '0.6'],
            ['club.islamique', 'Club Islamique', 'monthly', '0.6'],
            ['about', 'À propos', 'monthly', '0.5'],
            ['contact', 'Contact', 'monthly', '0.5'],
            ['faq', 'FAQ', 'monthly', '0.5'],
            ['terms', 'Conditions générales', 'yearly', '0.2'],
            ['privacy', 'Confidentialité', 'yearly', '0.2'],
            ['refunds', 'Remboursements', 'yearly', '0.2'],
        ];

        foreach ($static as [$name, $label, $freq, $priority]) {
            if (Route::has($name)) {
                $entries->push($this->entry(route($name), $label, null, $freq, $priority, self::LOCALES));
            }
        }

        // Cours publiés (hors cours inclus dans un pack : leur page redirige vers le pack).
        $inPack = CoursePackItem::query()
            ->whereNotNull('linked_course_id')
            ->whereHas('pack', fn ($q) => $q->where('is_published', true))
            ->pluck('linked_course_id');

        Course::query()
            ->where('is_published', true)
            ->whereNotIn('id', $inPack)
            ->orderBy('id')
            ->get(['id', 'slug', 'title', 'updated_at'])
            ->each(fn (Course $c) => $entries->push($this->entry(
                route('courses.show', $c), 'Cours : '.$c->title, $c->updated_at?->toAtomString(), 'weekly', '0.7', self::LOCALES
            )));

        BlogCategory::query()
            ->where('is_active', true)
            ->whereHas('posts', fn ($q) => $q->published())
            ->orderBy('sort_order')
            ->get()
            ->each(fn (BlogCategory $cat) => $entries->push($this->entry(
                route('blog.category', $cat->slug), 'Blog — '.$cat->name, $cat->updated_at?->toAtomString(), 'weekly', '0.6', self::LOCALES
            )));

        BlogPost::query()
            ->published()
            ->latest('published_at')
            ->get()
            ->each(fn (BlogPost $post) => $entries->push($this->entry(
                $post->url(), 'Article : '.$post->title, $post->updated_at?->toAtomString(), 'monthly', '0.7', $post->availableLocales()
            )));

        return $entries;
    }

    /** @param  array<int, string>  $locales */
    private function entry(string $url, string $label, ?string $lastmod, string $freq, string $priority, array $locales): array
    {
        return [
            'loc' => $url,
            'label' => $label,
            'lastmod' => $lastmod,
            'changefreq' => $freq,
            'priority' => $priority,
            // Langues + variantes régionales des marchés prioritaires (fr-TN, ar-SA, fr-CA…).
            'alternates' => Hreflang::alternates($url, $locales ?: ['fr']),
        ];
    }
}
