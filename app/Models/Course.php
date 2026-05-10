<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CoursePackItem;
use App\Models\Enrollment;

class Course extends Model
{
    protected $fillable = [
        'subject_id',

        'slug',
        'title', 'title_ar', 'title_en',

        'description', 'description_ar', 'description_en',

        'delivery_type',
        'content_path',
        'pack_pdf_paths',
        
        'access_note',

        'is_paid',
        'price_cents',
        'currency',

        'thumbnail_path',

        'is_published',
        'has_preview',
        'sort_order',

        'difficulty_level',
        'duration_minutes',
        'is_featured',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_published' => 'boolean',
        'has_preview' => 'boolean',
        'is_featured' => 'boolean',
        'price_cents' => 'integer',
        'sort_order' => 'integer',
        'duration_minutes' => 'integer',
        'pack_pdf_paths' => 'array',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    public function getPriceAttribute(): float
    {
        return (float) (($this->price_cents ?? 0) / 100);
    }

    public function getTitleDisplayAttribute(): string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'ar' => $this->title_ar ?: ($this->title ?? ''),
            'en' => $this->title_en ?: ($this->title ?? ''),
            default => $this->title ?? '',
        };
    }

    public function getDescriptionDisplayAttribute(): string
    {
        $locale = app()->getLocale();
        $base = $this->description ?? '';

        return match ($locale) {
            'ar' => $this->description_ar ?: $base,
            'en' => $this->description_en ?: $base,
            default => $base,
        };
    }

    public function getTitleI18nAttribute(): string
    {
        return $this->title_display;
    }

    public function getDescriptionI18nAttribute(): string
    {
        return $this->description_display;
    }

    public function packItems(): HasMany
    {
        return $this->hasMany(CoursePackItem::class, 'pack_id')->orderBy('sort_order');
    }

    public function includedInPackItems(): HasMany
    {
        return $this->hasMany(CoursePackItem::class, 'linked_course_id');
    }

    public function isPack(): bool
    {
        return $this->delivery_type === 'pack';
    }

    public function isSimpleCourse(): bool
    {
        return in_array($this->delivery_type, ['course', 'series', 'exam'], true);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class);
    }

    public function getDurationLabelAttribute(): ?string
    {
        if (! $this->duration_minutes) {
            return null;
        }

        $h = intdiv($this->duration_minutes, 60);
        $m = $this->duration_minutes % 60;

        return $h > 0
            ? ($m > 0 ? "{$h}h {$m}min" : "{$h}h")
            : "{$m}min";
    }

    public function itemProgress()
    {
        return $this->hasMany(CoursePackItemProgress::class, 'pack_id');
    }
}