<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseItemAnswer extends Model
{
    protected $fillable = [
        'exercise_item_id',

        'answer_text',
        'answer_text_ar',
        'answer_text_en',

        'match_text',
        'match_text_ar',
        'match_text_en',

        'image_path',

        'category',
        'category_ar',
        'category_en',

        'is_correct',
        'correct_value',
        'tolerance',
        'sort_order',
        'blank_index',
        'match_mode',
    ];

    protected $casts = [
        'exercise_item_id' => 'integer',
        'is_correct'       => 'boolean',
        'correct_value'    => 'decimal:4',
        'tolerance'        => 'decimal:4',
        'sort_order'       => 'integer',
        'blank_index'      => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ExerciseItem::class, 'exercise_item_id');
    }
}
