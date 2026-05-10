<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id',
        'user_id',
        'attempt_number',
        'score',
        'max_score',
        'percentage',
        'is_passed',
        'started_at',
        'submitted_at',
    ];

    protected $casts = [
        'quiz_id' => 'integer',
        'user_id' => 'integer',
        'attempt_number' => 'integer',
        'score' => 'integer',
        'max_score' => 'integer',
        'percentage' => 'decimal:2',
        'is_passed' => 'boolean',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }
    public function getStatusLabelAttribute(): string
    {
        return match (true) {
            $this->percentage >= 90 => 'Excellent',
            $this->percentage >= 75 => 'Très bien',
            $this->percentage >= 50 => 'Bon résultat',
            default => 'À renforcer',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match (true) {
            $this->percentage >= 90 => 'emerald',
            $this->percentage >= 75 => 'blue',
            $this->percentage >= 50 => 'amber',
            default => 'rose',
        };
    }
}