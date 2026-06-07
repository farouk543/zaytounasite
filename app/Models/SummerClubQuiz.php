<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SummerClubQuiz extends Model
{
    protected $fillable = [
        'summer_club_resource_id',
        'title',
        'description',
        'subject',
        'level',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'summer_club_resource_id' => 'integer',
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(SummerClubResource::class, 'summer_club_resource_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SummerClubQuizQuestion::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(SummerClubQuizAttempt::class);
    }

    public function getTotalPointsAttribute(): int
    {
        return (int) $this->questions()->sum('points');
    }
}
