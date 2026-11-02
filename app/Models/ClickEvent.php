<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class ClickEvent extends Model
{
    use Prunable;

    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = ['created_at' => 'datetime'];

    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subMonths(25));
    }
}
