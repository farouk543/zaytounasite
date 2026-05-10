<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'course_id',
        'unit_price_cents',
        'qty',
    ];

    protected $casts = [
        'unit_price_cents' => 'integer',
        'qty' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function getLineTotalCentsAttribute(): int
    {
        return (int)$this->unit_price_cents * (int)$this->qty;
    }
}