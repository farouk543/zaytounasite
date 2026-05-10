<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Enrollment extends Model
{
    protected $fillable = [
        'user_id', 'course_id', 'enrolled_at', 'access_ends_at', 'status',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'access_ends_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }

    public function isActive(): bool
    {
        if (($this->status ?? 'active') !== 'active') return false;

        if ($this->access_ends_at && $this->access_ends_at->isPast()) return false;

        return true;
    }
    
}