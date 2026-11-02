<?php

namespace App\Models;

use App\Services\Blog\ArticleRenderer;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    public const LOCALES = ['fr', 'ar', 'en'];

    protected $fillable = [
        'blog_category_id', 'author_id', 'slug',
        'title', 'title_ar', 'title_en',
        'excerpt', 'excerpt_ar', 'excerpt_en',
        'content', 'content_ar', 'content_en',
        'cover_image',
        'meta_title', 'meta_title_ar', 'meta_title_en',
        'meta_description', 'meta_description_ar', 'meta_description_en',
        'focus_keyword', 'target_countries', 'status', 'published_at', 'is_featured', 'views_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'target_countries' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (BlogPost $post) {
            if ($post->status === 'published' && ! $post->published_at) {
                $post->published_at = now();
            }
        });

        $flush = fn () => Cache::forget('seo.sitemap.xml');
        static::saved($flush);
        static::deleted($flush);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** Articles visibles sur le site : publiés et date de publication atteinte. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /** L'article vise-t-il ce pays ? (aucun pays coché = article pour tous). */
    public function targetsCountry(?string $country): bool
    {
        $targets = $this->target_countries ?: [];

        return $country !== null && in_array(strtoupper($country), $targets, true);
    }

    /** Noms des marchés visés, ex. « 🇹🇳 Tunisie · 🇫🇷 France ». */
    public function targetCountriesLabel(): string
    {
        $markets = config('marketing.markets');

        return collect($this->target_countries ?: [])
            ->map(fn ($cc) => isset($markets[$cc]) ? $markets[$cc]['flag'].' '.$markets[$cc]['name'] : $cc)
            ->implode(' · ');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at && $this->published_at->lte(now());
    }

    /** Valeur d'un champ traduit pour une langue, avec repli sur le français. */
    public function localized(string $field, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $value = $locale === 'fr' ? null : $this->{$field.'_'.$locale};

        return filled($value) ? $value : $this->{$field};
    }

    /** Langues dans lesquelles l'article est réellement rédigé (titre + contenu). */
    public function availableLocales(): array
    {
        return array_values(array_filter(self::LOCALES, function (string $locale) {
            $suffix = $locale === 'fr' ? '' : '_'.$locale;

            return filled($this->{'title'.$suffix}) && filled($this->{'content'.$suffix});
        }));
    }

    public function getTitleDisplayAttribute(): string
    {
        return (string) $this->localized('title');
    }

    public function getExcerptDisplayAttribute(): string
    {
        $excerpt = $this->localized('excerpt');

        return filled($excerpt)
            ? $excerpt
            : Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($this->contentHtml()))), 180);
    }

    public function getMetaTitleDisplayAttribute(): string
    {
        return (string) ($this->localized('meta_title') ?: $this->title_display);
    }

    public function getMetaDescriptionDisplayAttribute(): string
    {
        return Str::limit((string) ($this->localized('meta_description') ?: $this->excerpt_display), 160, '…');
    }

    public function contentHtml(?string $locale = null): string
    {
        $content = $this->localized('content', $locale);

        if (blank($content)) {
            return '';
        }

        return RichContentRenderer::make($content)
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsVisibility('public')
            ->toHtml();
    }

    /** @var array<string, array> */
    private array $renderedArticles = [];

    /**
     * Article prêt à afficher : HTML enrichi, sommaire et FAQ.
     *
     * @return array{html: string, toc: array, faq: array}
     */
    public function article(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return $this->renderedArticles[$locale] ??= app(ArticleRenderer::class)
            ->render($this->contentHtml($locale), __('blog.key_point'));
    }

    public function getReadingMinutesAttribute(): int
    {
        // preg_split plutôt que str_word_count, qui ignore les mots arabes.
        $text = trim(strip_tags((string) $this->localized('content')));
        $words = $text === '' ? 0 : count(preg_split('/\s+/u', $text));

        return max(1, (int) ceil($words / 200));
    }

    public function getCoverUrlAttribute(): ?string
    {
        // asset() plutôt que Storage::url() : suit l'adresse réelle du site (sous-dossier compris),
        // même si APP_URL est mal renseigné.
        return $this->cover_image && Storage::disk('public')->exists($this->cover_image)
            ? asset('storage/'.ltrim($this->cover_image, '/'))
            : null;
    }

    public function url(?string $locale = null): string
    {
        $url = route('blog.show', $this->slug);

        return $locale && $locale !== 'fr' ? $url.'?lang='.$locale : $url;
    }
}
