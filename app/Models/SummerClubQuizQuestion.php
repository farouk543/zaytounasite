<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SummerClubQuizQuestion extends Model
{
    protected $fillable = [
        'summer_club_quiz_id',
        'question',
        'media_type',
        'media_path',
        'media_url',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
        'explanation',
        'points',
        'sort_order',
    ];

    protected $casts = [
        'summer_club_quiz_id' => 'integer',
        'points' => 'integer',
        'sort_order' => 'integer',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(SummerClubQuiz::class, 'summer_club_quiz_id');
    }
}
