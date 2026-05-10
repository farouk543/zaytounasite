<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    protected $fillable = [
        'track_id', 'slug', 'name', 'name_ar', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class)->orderBy('sort_order');
    }

    // temporaire, pour compatibilité avec l'existant
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class)->orderBy('sort_order');
    }

    public function getNameI18nAttribute(): string
    {
        $loc = app()->getLocale();

        return match ($loc) {
            'ar' => $this->name_ar ?: ($this->name ?? ''),
            default => $this->name ?? '',
        };
    }
}