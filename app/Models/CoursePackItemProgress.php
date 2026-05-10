<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoursePackItemProgress extends Model
{
    protected $table = 'course_pack_item_progress';

    protected $fillable = [
        'user_id',
        'pack_id',
        'course_pack_item_id',
        'is_started',
        'is_completed',
        'started_at',
        'completed_at',
        'last_seen_at',
        'progress_percent',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'pack_id' => 'integer',
        'course_pack_item_id' => 'integer',
        'is_started' => 'boolean',
        'is_completed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'progress_percent' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pack(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'pack_id');
    }

    public function packItem(): BelongsTo
    {
        return $this->belongsTo(CoursePackItem::class, 'course_pack_item_id');
    }
}