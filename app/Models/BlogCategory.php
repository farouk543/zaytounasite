<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class BlogCategory extends Model
{
    protected $fillable = [
        'name', 'name_ar', 'name_en', 'slug',
        'description', 'description_ar', 'description_en',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget('seo.sitemap.xml');
        static::saved($flush);
        static::deleted($flush);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    public function getNameDisplayAttribute(): string
    {
        return match (app()->getLocale()) {
            'ar' => $this->name_ar ?: $this->name,
            'en' => $this->name_en ?: $this->name,
            default => $this->name,
        };
    }

    public function getDescriptionDisplayAttribute(): ?string
    {
        return match (app()->getLocale()) {
            'ar' => $this->description_ar ?: $this->description,
            'en' => $this->description_en ?: $this->description,
            default => $this->description,
        };
    }
}
