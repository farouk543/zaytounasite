<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SummerClubResource extends Model
{
    protected $fillable = [
        'title',
        'type',
        'subject',
        'level',
        'description',
        'content',
        'file_path',
        'cover_image_path',
        'correction_content',
        'correction_file_path',
        'is_published',
        'is_locked',
        'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_locked' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function quizzes(): HasMany
    {
        return $this->hasMany(SummerClubQuiz::class)->orderBy('sort_order');
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(SummerClubExercise::class)->orderBy('sort_order');
    }
}
