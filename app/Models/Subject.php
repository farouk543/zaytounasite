<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'level_id',
        'branch_id',
        'slug',
        'name',
        'name_ar',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class)->orderBy('sort_order');
    }

    public function getTrackAttribute()
    {
        return $this->branch?->track ?? $this->level?->track;
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