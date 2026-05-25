<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SummerClubSubscriptionRequest extends Model
{
    public const SUBJECTS = [
        'Français',
        'Anglais',
        'Mathématiques',
        'Coran',
    ];

    public const PACKS = [
        'essential' => [
            'name' => 'Pack Essentiel',
            'feature' => '1 matière au choix',
            'description' => 'Idéal pour renforcer une compétence précise.',
            'price' => 120,
            'old_price' => null,
            'duration_months' => 3,
            'subject_count' => 1,
            'subjects' => null,
            'badge' => null,
        ],
        'duo' => [
            'name' => 'Pack Duo',
            'feature' => '2 matières au choix',
            'description' => 'Pour progresser dans deux domaines importants.',
            'price' => 200,
            'old_price' => 240,
            'duration_months' => 3,
            'subject_count' => 2,
            'subjects' => null,
            'badge' => null,
        ],
        'complete' => [
            'name' => 'Pack Complet',
            'feature' => 'Toutes les matières disponibles',
            'description' => 'Le meilleur choix pour préparer la rentrée.',
            'price' => 270,
            'old_price' => 360,
            'duration_months' => 3,
            'subject_count' => 4,
            'subjects' => self::SUBJECTS,
            'badge' => 'Le plus recommandé',
        ],
    ];

    protected $fillable = [
        'user_id',
        'parent_name',
        'student_name',
        'phone',
        'email',
        'pack_key',
        'pack_name',
        'selected_subjects',
        'price',
        'duration_months',
        'status',
        'admin_notes',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
    ];

    protected $casts = [
        'selected_subjects' => 'array',
        'price' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public static function packDefinitions(): array
    {
        return self::PACKS;
    }

    public static function subjects(): array
    {
        return self::SUBJECTS;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
