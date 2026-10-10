<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'subtotal_cents',
        'discount_cents',
        'total_cents',
        'currency',
        'coupon_code',
        'type',
        'regime',
        'pack',
        'plan',
        'selection',
        'parent_name',
        'student_name',
        'phone',
        'contact_email',
        'customer_notes',
        'admin_notes',
        'platform_username',
        'approved_at',
        'approved_by',
        'access_sent_at',
    ];

    protected $casts = [
        'subtotal_cents' => 'integer',
        'discount_cents' => 'integer',
        'total_cents' => 'integer',
        'selection' => 'array',
        'approved_at' => 'datetime',
        'access_sent_at' => 'datetime',
    ];

    public function isRegime(): bool
    {
        return $this->type === 'regime';
    }

    /** Adresse à laquelle écrire au parent : celle saisie à la demande, sinon celle du compte. */
    public function parentEmail(): ?string
    {
        return $this->contact_email ?: $this->user?->email;
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}