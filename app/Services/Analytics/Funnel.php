<?php

namespace App\Services\Analytics;

use App\Models\Order;
use App\Models\PageView;
use App\Support\Markets;

/**
 * Parcours complet d'un mois, par pays prioritaire :
 * visiteurs du site → demandes d'abonnement (régimes) → demandes acceptées → comptes créés sur la plateforme
 * → élèves venus du site présents en séance → élèves fidèles (présents ce mois ET le mois précédent).
 *
 * Les 3 premières étapes viennent du site ; les suivantes de l'API de la plateforme (PlatformStats).
 * Le pays d'une commande est celui des pages vues par son auteur (fourni par Cloudflare).
 */
class Funnel
{
    public const STAGES = [
        'visitors' => 'Visiteurs',
        'orders' => 'Demandes',
        'paid' => 'Acceptées',
        'accounts' => 'Comptes créés',
        'active' => 'Élèves actifs',
        'retained' => 'Élèves fidèles',
    ];

    public function __construct(private GoalTracker $goals, private PlatformStats $platform) {}

    /**
     * @return array{month: string, platform: bool, rows: array<int, array>, total: array, leak: ?array}
     */
    public function month(string $month): array
    {
        [$from, $to] = $this->goals->monthRange($month);
        $platform = $this->platform->month($month);

        $visitors = PageView::query()->whereBetween('created_at', [$from, $to])
            ->selectRaw('country as k, COUNT(DISTINCT visitor_hash) as v')->groupBy('k')->pluck('v', 'k');

        $userCountry = PageView::query()
            ->whereNotNull('user_id')->whereNotNull('country')->where('country', '!=', '')
            ->selectRaw('user_id, MAX(country) as country')->groupBy('user_id')->pluck('country', 'user_id');

        $orders = Order::query()->where('type', 'regime')->whereBetween('created_at', [$from, $to])->get(['user_id', 'status']);
        $orderCountry = fn ($o) => $userCountry[$o->user_id] ?? null;

        $rows = collect(array_keys(Markets::all()))->map(function (string $cc) use ($visitors, $orders, $orderCountry, $platform) {
            $countryOrders = $orders->filter(fn ($o) => $orderCountry($o) === $cc);

            return $this->withRates([
                'code' => $cc,
                'name' => Markets::name($cc, 'fr'),
                'visitors' => (int) ($visitors[$cc] ?? 0),
                'orders' => $countryOrders->count(),
                'paid' => $countryOrders->where('status', 'paid')->count(),
                'accounts' => $platform ? (int) data_get($platform, "by_country.$cc.site_accounts_new", 0) : null,
                'active' => $platform ? (int) data_get($platform, "by_country.$cc.site_students_active", 0) : null,
                'retained' => $platform ? (int) data_get($platform, "by_country.$cc.retained_students", 0) : null,
            ]);
        })->values()->all();

        $total = $this->withRates([
            'code' => 'ALL',
            'name' => 'Tous pays',
            'visitors' => (int) PageView::query()->whereBetween('created_at', [$from, $to])->distinct()->count('visitor_hash'),
            'orders' => $orders->count(),
            'paid' => $orders->where('status', 'paid')->count(),
            'accounts' => $platform ? (int) data_get($platform, 'from_site.accounts_new', 0) : null,
            'active' => $platform ? (int) data_get($platform, 'from_site.active', 0) : null,
            'retained' => $platform ? (int) data_get($platform, 'retained_students', 0) : null,
        ]);

        return [
            'month' => $month,
            'platform' => $platform !== null,
            'rows' => $rows,
            'total' => $total,
            'leak' => $this->leak($total),
        ];
    }

    /** Ajoute le taux de passage de chaque étape à la suivante (en %), null si l'étape précédente est vide. */
    private function withRates(array $row): array
    {
        $keys = array_keys(self::STAGES);
        $row['rates'] = [];
        for ($i = 1; $i < count($keys); $i++) {
            $prev = $row[$keys[$i - 1]];
            $cur = $row[$keys[$i]];
            $row['rates'][$keys[$i]] = ($prev === null || $cur === null || $prev <= 0) ? null : (int) round($cur / $prev * 100);
        }

        return $row;
    }

    /** L'étape où l'on perd le plus de familles (taux de passage le plus faible), pour savoir où agir. */
    private function leak(array $total): ?array
    {
        $keys = array_keys(self::STAGES);
        $worst = null;
        foreach ($total['rates'] as $stage => $rate) {
            if ($rate === null) {
                continue;
            }
            $prev = $keys[array_search($stage, $keys, true) - 1];
            // Visiteurs → demandes : un taux de quelques % est normal ; on le compare à 5 % plutôt qu'à 100 %.
            $score = $stage === 'orders' ? $rate / 5 * 100 : $rate;
            if ($worst === null || $score < $worst['score']) {
                $worst = ['from' => self::STAGES[$prev], 'to' => self::STAGES[$stage], 'rate' => $rate, 'score' => $score, 'stage' => $stage];
            }
        }

        if ($worst === null || $worst['score'] >= 80) {
            return null;
        }

        $worst['advice'] = match ($worst['stage']) {
            'orders' => 'Peu de visiteurs passent à la demande : mettez le bouton WhatsApp et « Commencer » plus en avant sur les pages régimes, et proposez un cours d’essai.',
            'paid' => 'Beaucoup de demandes ne sont pas acceptées : rappelez les familles dans les 24 h et relancez celles qui n’ont pas répondu.',
            'accounts' => 'Des demandes acceptées n’ont pas encore de compte sur la plateforme : utilisez « Créer le compte sur la plateforme » dans Abonnements live.',
            'active' => 'Des élèves ont un compte mais ne viennent pas en séance : planifiez leur première séance dans la semaine et envoyez le lien aux parents.',
            'retained' => 'Les élèves ne reviennent pas le mois suivant : appelez les parents des élèves absents et proposez un bilan de progression.',
            default => null,
        };

        return $worst;
    }
}
