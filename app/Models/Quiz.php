<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = [
        'course_id',
        'course_pack_item_id',

        'title',
        'title_ar',
        'title_en',

        'description',
        'description_ar',
        'description_en',

        'passing_score',
        'time_limit_minutes',
        'max_attempts',

        'is_randomized',
        'show_result_immediately',
        'is_published',
    ];

    protected $casts = [
        'course_id' => 'integer',
        'course_pack_item_id' => 'integer',
        'passing_score' => 'integer',
        'time_limit_minutes' => 'integer',
        'max_attempts' => 'integer',
        'is_randomized' => 'boolean',
        'show_result_immediately' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function packItem(): BelongsTo
    {
        return $this->belongsTo(CoursePackItem::class, 'course_pack_item_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
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

    public function getMaxPossibleScoreAttribute(): int
    {
        return (int) $this->questions()->sum('points');
    }
    public function getQuestionsCountAttribute(): int
    {
        return $this->questions()->count();
    }
}