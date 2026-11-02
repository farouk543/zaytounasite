<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class PageView extends Model
{
    use Prunable;

    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = ['created_at' => 'datetime'];

    /** On garde 25 mois d'historique pour comparer d'une année sur l'autre. */
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subMonths(25));
    }
}
