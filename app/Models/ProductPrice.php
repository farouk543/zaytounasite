<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProductPrice extends Model
{
    protected $fillable = [
        'priceable_type',
        'priceable_id',
        'currency',
        'price_cents',
    ];

    protected $casts = [
        'price_cents' => 'integer',
    ];

    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function setCurrencyAttribute($value): void
    {
        $this->attributes['currency'] = strtoupper((string) $value);
    }
}
