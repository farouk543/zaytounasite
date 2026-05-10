<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizOption extends Model
{
    protected $fillable = [
        'quiz_question_id',

        'option_text',
        'option_text_ar',
        'option_text_en',

        'match_text',
        'match_text_ar',
        'match_text_en',

        'is_correct',
        'sort_order',
    ];

    protected $casts = [
        'quiz_question_id' => 'integer',
        'is_correct' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }

    public function getOptionTextDisplayAttribute(): string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'ar' => $this->option_text_ar ?: ($this->option_text ?? ''),
            'en' => $this->option_text_en ?: ($this->option_text ?? ''),
            default => $this->option_text ?? '',
        };
    }

    public function getMatchTextDisplayAttribute(): string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'ar' => $this->match_text_ar ?: ($this->match_text ?? ''),
            'en' => $this->match_text_en ?: ($this->match_text ?? ''),
            default => $this->match_text ?? '',
        };
    }

    public function attemptAnswers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class, 'selected_option_id');
    }

    public function isChoiceBased(): bool
    {
        return in_array($this->question_type, ['single_choice', 'multiple_choice', 'true_false'], true);
    }
}