<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseAttemptAnswer extends Model
{
    protected $fillable = [
        'exercise_attempt_id',
        'exercise_item_id',
        'answer_data',
        'is_correct',
        'points_earned',
    ];

    protected $casts = [
        'answer_data'   => 'array',
        'is_correct'    => 'boolean',
        'points_earned' => 'integer',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExerciseAttempt::class, 'exercise_attempt_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ExerciseItem::class, 'exercise_item_id');
    }
}
