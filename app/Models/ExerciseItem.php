<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExerciseItem extends Model
{
    protected $fillable = [
        'exercise_id',
        'item_type',

        'statement',
        'statement_ar',
        'statement_en',

        'image_path',

        'hint',
        'hint_ar',
        'hint_en',

        'explanation',
        'explanation_ar',
        'explanation_en',

        'points',
        'sort_order',
    ];

    protected $casts = [
        'exercise_id' => 'integer',
        'points'      => 'integer',
        'sort_order'  => 'integer',
    ];

    // ── Relations ────────────────────────────────────────────────────────

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExerciseItemAnswer::class)->orderBy('sort_order');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    public function getItemTypeLabelAttribute(): string
    {
        return match ($this->item_type) {
            'single_choice'  => 'QCM — Choix unique',
            'multiple_choice'=> 'QCM — Choix multiple',
            'true_false'     => 'Vrai / Faux',
            'matching'       => 'Relier par une flèche',
            'ordering'       => 'Remettre dans l\'ordre',
            'fill_blank'     => 'Texte à trous',
            'word_bank'      => 'Banque de mots',
            'categorization' => 'Classer par catégorie',
            'short_answer'   => 'Réponse courte',
            'calculation'    => 'Calcul numérique',
            default          => $this->item_type,
        };
    }

    /**
     * Types qui ne nécessitent PAS de champ is_correct
     * (leur correction est portée par la structure des réponses).
     */
    public function isStructuralType(): bool
    {
        return in_array($this->item_type, ['matching', 'ordering', 'categorization'], true);
    }
}
