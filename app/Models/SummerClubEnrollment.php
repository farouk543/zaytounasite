<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SummerClubEnrollment extends Model
{
    protected $fillable = [
        'user_id',
        'pack_name',
        'pack_key',
        'selected_subjects',
        'level',
        'status',
        'starts_at',
        'expires_at',
        'confirmed_at',
        'confirmed_by',
        'notes',
    ];

    protected $casts = [
        'selected_subjects' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(SummerClubQuizAttempt::class);
    }

    public function exerciseAttempts(): HasMany
    {
        return $this->hasMany(SummerClubExerciseAttempt::class);
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
