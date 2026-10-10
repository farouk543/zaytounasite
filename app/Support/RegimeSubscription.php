<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\Level;
use App\Models\Order;
use App\Models\Subject;

/**
 * Libellés et résumé lisible d'une demande d'abonnement « régime » (cours live),
 * partagés par le back-office, les notifications et les e-mails.
 */
class RegimeSubscription
{
    public const REGIMES = [
        'tunisia' => 'Tunisie',
        'france'  => 'France',
        'qatar'   => 'Qatar',
        'saudi'   => 'Arabie Saoudite',
        'quran'   => 'Coran',
    ];

    public const PACKS = [
        'individual' => 'Pack Individuel',
        'duo'        => 'Pack Duo',
        'group'      => 'Pack Groupe',
        'recorded'   => 'Contenu enregistré',
        'custom'     => 'Pack Personnalisé',
    ];

    public const PLANS = [
        'monthly'   => 'Mensuel',
        'quarterly' => 'Trimestriel',
        'yearly'    => 'Annuel',
    ];

    public const STATUSES = [
        'pending_manual' => 'À traiter',
        'paid'           => 'Acceptée',
        'canceled'       => 'Refusée',
        'refunded'       => 'Remboursée',
    ];

    public static function regimeLabel(?string $slug): string
    {
        return self::REGIMES[$slug] ?? ($slug ?: '—');
    }

    public static function packLabel(?string $pack): string
    {
        return self::PACKS[$pack] ?? ($pack ?: '—');
    }

    public static function planLabel(?string $plan): ?string
    {
        return $plan ? (self::PLANS[$plan] ?? $plan) : null;
    }

    public static function statusLabel(?string $status): string
    {
        return self::STATUSES[$status] ?? ($status ?: '—');
    }

    public static function totalLabel(Order $order): string
    {
        if ($order->total_cents <= 0) {
            return 'À définir';
        }

        $amount = number_format($order->total_cents / 100, 2, ',', ' ') . ' ' . ($order->currency ?? 'TND');

        // Pack classique : prix mensuel ; pack personnalisé : total de la période choisie
        return $order->pack === 'custom'
            ? $amount . ' (' . mb_strtolower(self::planLabel($order->plan) ?? '') . ')'
            : $amount . ' / mois';
    }

    /**
     * Une ligne par matière demandée, ex. « 3ème année — Mathématiques ».
     * Pour un pack personnalisé : « Ali — 7ème — Arabe — 1h × 4 séances/mois ».
     *
     * @return list<string>
     */
    public static function detailLines(Order $order): array
    {
        $sel = $order->selection ?? [];

        $rows = ($sel['pack'] ?? $order->pack) === 'custom'
            ? ($sel['items'] ?? [])
            : [$sel];

        $names = self::nameMaps($rows);

        return collect($rows)
            ->map(function (array $row) use ($names) {
                $parts = array_filter([
                    trim((string) ($row['child'] ?? '')) ?: null,
                    $names['levels'][$row['level_id'] ?? 0] ?? null,
                    $row['grade'] ?? null,
                    $names['branches'][$row['branch_id'] ?? 0] ?? null,
                    $names['subjects'][$row['subject_id'] ?? 0] ?? null,
                    ! empty($row['duration_h'])
                        ? $row['duration_h'] . 'h × ' . ($row['sessions_per_month'] ?? '?') . ' séance(s)/mois'
                        : null,
                ]);

                return implode(' — ', $parts);
            })
            ->filter()
            ->values()
            ->all();
    }

    /** Résumé court pour une cellule de tableau / un titre de notification. */
    public static function shortSummary(Order $order): string
    {
        $lines = self::detailLines($order);

        if (count($lines) > 1) {
            return count($lines) . ' matières';
        }

        return $lines[0] ?? '—';
    }

    private static function nameMaps(array $rows): array
    {
        $ids = fn (string $key) => collect($rows)->pluck($key)->filter()->unique()->values()->all();

        return [
            'levels'   => Level::query()->whereIn('id', $ids('level_id'))->pluck('name', 'id')->all(),
            'branches' => Branch::query()->whereIn('id', $ids('branch_id'))->pluck('name', 'id')->all(),
            'subjects' => Subject::query()->whereIn('id', $ids('subject_id'))->pluck('name', 'id')->all(),
        ];
    }
}
