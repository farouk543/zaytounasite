<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExerciseAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'exercise_id',
        'score',
        'max_score',
        'is_completed',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'score'        => 'integer',
        'max_score'    => 'integer',
        'is_completed' => 'boolean',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExerciseAttemptAnswer::class);
    }

    public function getPercentageAttribute(): int
    {
        if (! $this->max_score) return 0;
        return (int) round(($this->score / $this->max_score) * 100);
    }
}
