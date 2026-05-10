<?php

namespace App\Models;

use App\Models\Exercise;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoursePackItem extends Model
{
    protected $fillable = [
        'pack_id',
        'item_type',

        'title',
        'title_ar',
        'title_en',

        'description',
        'description_ar',
        'description_en',

        'linked_course_id',
        'resource_path',
        'resource_paths',
        'external_url',
        'video_url',

        'duration_minutes',
        'is_required',
        'is_preview',
        'sort_order',
    ];

    protected $casts = [
        'linked_course_id' => 'integer',
        'resource_paths' => 'array',
        'duration_minutes' => 'integer',
        'is_required' => 'boolean',
        'is_preview' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function pack(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'pack_id');
    }

    public function linkedCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'linked_course_id');
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

    public function isQuiz(): bool
    {
        return $this->item_type === 'quiz';
    }

    public function isCourse(): bool
    {
        return $this->item_type === 'course';
    }

    public function isSeries(): bool
    {
        return $this->item_type === 'series';
    }

    public function isExamPrep(): bool
    {
        return $this->item_type === 'exam_prep';
    }

    public function isResource(): bool
    {
        return $this->item_type === 'resource';
    }

    public function isExercise(): bool
    {
        return $this->item_type === 'exercise';
    }

    public function getItemTypeLabelAttribute(): string
    {
        return match ($this->item_type) {
            'course'    => 'Course liée',
            'series'    => 'Série',
            'quiz'      => 'Quiz',
            'exam_prep' => 'Préparation examen',
            'resource'  => 'Ressource',
            'exercise'  => 'Exercice',
            default     => '-',
        };
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class, 'course_pack_item_id');
    }

    public function exercise(): HasOne
    {
        return $this->hasOne(Exercise::class, 'course_pack_item_id');
    }
    public function progressRecords(): HasMany
    {
        return $this->hasMany(CoursePackItemProgress::class, 'course_pack_item_id');
    }
}