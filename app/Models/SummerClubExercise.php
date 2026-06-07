<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SummerClubExercise extends Model
{
    protected $fillable = [
        'summer_club_resource_id',
        'title',
        'description',
        'subject',
        'level',
        'cover_image_path',
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

    public function items(): HasMany
    {
        return $this->hasMany(SummerClubExerciseItem::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(SummerClubExerciseAttempt::class);
    }
}
