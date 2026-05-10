<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    protected $fillable = [
        'course_id',
        'course_pack_item_id',

        'exercise_type',
        'pdf_path',

        'is_paid',
        'price_cents',
        'currency',

        'audio_path',
        'video_path',

        'title',
        'title_ar',
        'title_en',

        'description',
        'description_ar',
        'description_en',

        'instructions',
        'instructions_ar',
        'instructions_en',

        'difficulty',
        'estimated_duration_minutes',

        'show_correction_immediately',
        'allow_retry',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'course_id'                    => 'integer',
        'course_pack_item_id'          => 'integer',
        'estimated_duration_minutes'   => 'integer',
        'show_correction_immediately'  => 'boolean',
        'allow_retry'                  => 'boolean',
        'is_published'                 => 'boolean',
        'sort_order'                   => 'integer',
        'is_paid'                      => 'boolean',
        'price_cents'                  => 'integer',
    ];

    // ── Relations ────────────────────────────────────────────────────────

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function packItem(): BelongsTo
    {
        return $this->belongsTo(CoursePackItem::class, 'course_pack_item_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExerciseItem::class)->orderBy('sort_order');
    }

    // ── Accessors ─────────────────────────────────────────────────────────

    public function getTitleDisplayAttribute(): string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'ar' => $this->title_ar ?: ($this->title ?? ''),
            'en' => $this->title_en ?: ($this->title ?? ''),
            default => $this->title ?? '',
        };
    }

    public function getTotalPointsAttribute(): int
    {
        return (int) $this->items()->sum('points');
    }

    public function getDifficultyLabelAttribute(): string
    {
        return match ($this->difficulty) {
            'easy'   => 'Facile',
            'medium' => 'Moyen',
            'hard'   => 'Difficile',
            default  => '—',
        };
    }
}
