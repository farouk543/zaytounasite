<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Avis de parents / élèves. Saisis dans le back-office ou envoyés via /avis (modérés avant publication).
 * Affichés sur les pages régimes et l'accueil, avec balisage AggregateRating à partir de 3 avis.
 */
class Testimonial extends Model
{
    public const PROGRAMS = [
        'tunisia' => 'Programme tunisien',
        'france' => 'Programme français',
        'qatar' => 'Programme qatari',
        'saudi' => 'Programme saoudien',
        'quran' => 'Coran & arabe',
        'general' => 'Zaytouna en général',
    ];

    protected $fillable = [
        'author_name', 'author_role', 'city', 'country', 'program', 'rating',
        'quote', 'quote_ar', 'quote_en', 'source', 'contact',
        'is_published', 'is_featured', 'sort_order', 'received_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'received_at' => 'date',
    ];

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    /** Avis d'un programme (+ avis généraux), les mis en avant d'abord. */
    public static function forProgram(?string $program, int $limit = 6): Collection
    {
        return static::published()
            ->when($program, fn ($q) => $q->whereIn('program', [$program, 'general']))
            ->orderByDesc('is_featured')
            ->when($program, fn ($q) => $q->orderByRaw('case when program = ? then 0 else 1 end', [$program]))
            ->orderBy('sort_order')
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /** @return array{count: int, average: float} sur tous les avis publiés du programme (+ généraux). */
    public static function summary(?string $program): array
    {
        $q = static::published()->when($program, fn ($q) => $q->whereIn('program', [$program, 'general']));

        return ['count' => (clone $q)->count(), 'average' => round((float) (clone $q)->avg('rating'), 1)];
    }

    public function quoteFor(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $localized = $locale === 'fr' ? null : $this->{'quote_'.$locale} ?? null;

        return filled($localized) ? $localized : $this->quote;
    }

    public function programLabel(): string
    {
        return self::PROGRAMS[$this->program] ?? $this->program;
    }
}
