<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Track extends Model
{
    protected $fillable = [
        'slug', 'name', 'name_ar', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function levels(): HasMany
    {
        return $this->hasMany(Level::class)->orderBy('sort_order');
    }

    /**
     * Localized name for Track (AR supported via name_ar).
     * EN uses 'name' for now (since name_en not in DB).
     */
    public function getNameI18nAttribute(): string
    {
        $loc = app()->getLocale();

        return match ($loc) {
            'ar' => $this->name_ar ?: ($this->name ?? ''),
            default => $this->name ?? '',
        };
    }
}