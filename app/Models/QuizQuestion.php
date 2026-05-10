<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizQuestion extends Model
{
    protected $fillable = [
        'quiz_id',
        'question_type',

        'question_text',
        'question_text_ar',
        'question_text_en',

        'explanation',
        'explanation_ar',
        'explanation_en',

        'points',
        'sort_order',
        'image_path',
    ];

    protected $casts = [
        'quiz_id' => 'integer',
        'points' => 'integer',
        'sort_order' => 'integer',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuizOption::class)->orderBy('sort_order');
    }

    public function getQuestionTextDisplayAttribute(): string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'ar' => $this->question_text_ar ?: ($this->question_text ?? ''),
            'en' => $this->question_text_en ?: ($this->question_text ?? ''),
            default => $this->question_text ?? '',
        };
    }

    public function getExplanationDisplayAttribute(): string
    {
        $locale = app()->getLocale();
        $base = $this->explanation ?? '';

        return match ($locale) {
            'ar' => $this->explanation_ar ?: $base,
            'en' => $this->explanation_en ?: $base,
            default => $base,
        };
    }

    public function isChoiceType(): bool
    {
        return in_array($this->question_type, ['single_choice', 'multiple_choice', 'true_false'], true);
    }

    public function isMatchingType(): bool
    {
        return $this->question_type === 'matching';
    }

    public function isOrderingType(): bool
    {
        return $this->question_type === 'ordering';
    }

    public function isFillBlankType(): bool
    {
        return $this->question_type === 'fill_blank';
    }
    public function attemptsAnswers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class, 'quiz_question_id');
    }
}