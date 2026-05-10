<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SummerClubExerciseItem extends Model
{
    protected $fillable = [
        'summer_club_exercise_id',
        'type',
        'instruction',
        'question',
        'media_type',
        'media_path',
        'media_url',
        'options',
        'correct_answer',
        'explanation',
        'points',
        'sort_order',
    ];

    protected $casts = [
        'summer_club_exercise_id' => 'integer',
        'options' => 'array',
        'correct_answer' => 'array',
        'points' => 'integer',
        'sort_order' => 'integer',
    ];

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(SummerClubExercise::class, 'summer_club_exercise_id');
    }

    public function setOptionsAttribute($value): void
    {
        $this->attributes['options'] = $this->jsonAttributeValue($value);
    }

    public function setCorrectAnswerAttribute($value): void
    {
        $this->attributes['correct_answer'] = $this->jsonAttributeValue($value);
    }

    private function jsonAttributeValue($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        $decoded = json_decode((string) $value, true);

        return json_last_error() === JSON_ERROR_NONE
            ? json_encode($decoded, JSON_UNESCAPED_UNICODE)
            : json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
