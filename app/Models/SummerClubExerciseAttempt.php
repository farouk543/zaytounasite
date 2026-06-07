<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SummerClubExerciseAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'summer_club_exercise_id',
        'summer_club_enrollment_id',
        'answers',
        'score',
        'total',
        'percentage',
        'passed',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'score' => 'integer',
        'total' => 'integer',
        'percentage' => 'decimal:2',
        'passed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(SummerClubExercise::class, 'summer_club_exercise_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(SummerClubEnrollment::class, 'summer_club_enrollment_id');
    }
}
