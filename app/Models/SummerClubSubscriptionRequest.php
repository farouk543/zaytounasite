<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class SummerClubSubscriptionRequest extends Model
{
    public const SUBJECTS = [
        'Français',
        'Anglais',
        'Mathématiques',
        'Coran',
        'Arabe',
    ];

    public const COMPLETE_PACK_SUBJECTS = [
        'Français',
        'Anglais',
        'Mathématiques',
    ];

    public const LEVELS = [
        '1' => 'Niveau 1',
        '2' => 'Niveau 2',
        '3' => 'Niveau 3',
    ];

    public const PACKS = [
        'essential' => [
            'name' => 'Pack 1 matière',
            'feature' => '1 matière au choix',
            'description' => 'Idéal pour renforcer une compétence précise.',
            'price' => 40,
            'old_price' => null,
            'duration_months' => 3,
            'subject_count' => 1,
            'subjects' => null,
            'badge' => null,
        ],
        'duo' => [
            'name' => 'Pack 2 matières',
            'feature' => '2 matières au choix',
            'description' => 'Pour progresser dans deux domaines importants.',
            'price' => 80,
            'old_price' => null,
            'duration_months' => 3,
            'subject_count' => 2,
            'subjects' => null,
            'badge' => null,
        ],
        'complete' => [
            'name' => 'Pack Complet',
            'feature' => 'Français + Anglais + Mathématiques',
            'description' => 'Le meilleur choix pour préparer la rentrée sur les matières principales.',
            'price' => 100,
            'old_price' => null,
            'duration_months' => 3,
            'subject_count' => 3,
            'subjects' => self::COMPLETE_PACK_SUBJECTS,
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

    public static function subjectOptions(): array
    {
        return array_combine(self::SUBJECTS, self::SUBJECTS);
    }

    public static function levelOptions(): array
    {
        return self::LEVELS;
    }

    public static function levelLabel(?string $level): string
    {
        if (blank($level)) {
            return '-';
        }

        return self::LEVELS[(string) $level] ?? 'Ancien niveau';
    }

    public static function normalizeLevel(mixed $level): ?string
    {
        if (blank($level)) {
            return null;
        }

        $level = (string) $level;

        if (! array_key_exists($level, self::LEVELS)) {
            throw ValidationException::withMessages([
                'level' => 'Niveau Club d’été invalide.',
            ]);
        }

        return $level;
    }

    public static function packOptions(): array
    {
        return collect(self::PACKS)
            ->mapWithKeys(fn (array $pack, string $key) => [$key => $pack['name']])
            ->all();
    }

    public static function statusOptions(): array
    {
        return [
            'pending' => 'En attente',
            'active' => 'Actif',
            'canceled' => 'Annulé',
            'expired' => 'Expiré',
        ];
    }

    public static function subjectsForPack(string $packKey, array $selectedSubjects = []): array
    {
        $pack = self::PACKS[$packKey] ?? null;

        if (! $pack) {
            throw ValidationException::withMessages([
                'pack_key' => 'Pack Club d’été invalide.',
            ]);
        }

        if ($pack['subjects'] !== null) {
            return $pack['subjects'];
        }

        $subjects = array_values(array_unique(array_filter($selectedSubjects)));
        $invalidSubjects = array_diff($subjects, self::SUBJECTS);

        if ($invalidSubjects !== []) {
            throw ValidationException::withMessages([
                'selected_subjects' => 'Une ou plusieurs matières sélectionnées sont invalides.',
            ]);
        }

        if (count($subjects) !== (int) $pack['subject_count']) {
            throw ValidationException::withMessages([
                'selected_subjects' => "Veuillez sélectionner {$pack['subject_count']} matière(s) pour ce pack.",
            ]);
        }

        return $subjects;
    }

    public static function normalizeEnrollmentData(array $data): array
    {
        $packKey = (string) ($data['pack_key'] ?? '');
        $pack = self::PACKS[$packKey] ?? null;

        if (! $pack) {
            throw ValidationException::withMessages([
                'pack_key' => 'Pack Club d’été invalide.',
            ]);
        }

        $data['pack_name'] = $pack['name'];
        $data['selected_subjects'] = self::subjectsForPack($packKey, $data['selected_subjects'] ?? []);
        $data['level'] = self::normalizeLevel($data['level'] ?? null);

        if (($data['status'] ?? null) === 'active') {
            $data['starts_at'] ??= now();
            $data['expires_at'] ??= now()->addMonths((int) $pack['duration_months']);
            $data['confirmed_at'] ??= now();
            $data['confirmed_by'] ??= auth()->id();
        }

        return $data;
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
