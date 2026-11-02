<?php

namespace App\Services\Analytics;

use App\Models\Order;
use App\Models\PageView;
use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * Bilan d'un mois (envoyé par e-mail le 1er du mois suivant) : note de la stratégie, chiffres du site,
 * chiffres de la plateforme, parcours complet et 3 actions prioritaires.
 */
class MonthlyReport
{
    public function __construct(
        private GoalTracker $goals,
        private PlatformStats $platform,
        private Funnel $funnel,
    ) {}

    /** Mois précédent (fuseau de Tunis) : celui qu'on fait le 1er du mois. */
    public static function previousMonth(): string
    {
        return CarbonImmutable::now(config('marketing.timezone'))->subMonthNoOverflow()->format('Y-m');
    }

    /** @return array<string, mixed> */
    public function build(string $month): array
    {
        [$from, $to] = $this->goals->monthRange($month);
        $prev = CarbonImmutable::createFromFormat('Y-m-d', $month.'-01')->subMonthNoOverflow()->format('Y-m');
        [$pFrom, $pTo] = $this->goals->monthRange($prev);

        $visitors = fn ($a, $b) => (int) PageView::whereBetween('created_at', [$a, $b])->distinct()->count('visitor_hash');
        $orders = Order::query()->whereBetween('created_at', [$from, $to]);

        $tips = collect($this->goals->recommendations($month));
        // Les conseils « à agir » d'abord, puis les informations.
        $actions = $tips->sortBy(fn ($t) => ['danger' => 0, 'warning' => 1, 'success' => 3][$t['level']] ?? 2)
            ->pluck('text')->take(3)->values()->all();

        $funnel = $this->funnel->month($month);
        if (! empty($funnel['leak']['advice']) && count($actions) < 3) {
            $actions[] = $funnel['leak']['advice'];
        }

        return [
            'month' => $month,
            'label' => ucfirst(CarbonImmutable::createFromFormat('Y-m-d', $month.'-01')->locale('fr')->isoFormat('MMMM YYYY')),
            'score' => $this->goals->score($month),
            'goals' => $this->goals->progress($month)->map(fn ($r) => [
                'label' => $r->label, 'scope' => $r->scope, 'actual' => $r->actual, 'target' => $r->target,
                'unit' => $r->unit, 'status' => $r->status,
            ])->all(),
            'site' => [
                'visitors' => $visitors($from, $to),
                'visitors_previous' => $visitors($pFrom, $pTo),
                'pageviews' => (int) PageView::whereBetween('created_at', [$from, $to])->count(),
                'signups' => (int) User::whereBetween('created_at', [$from, $to])->count(),
                'orders' => (int) (clone $orders)->count(),
                'paid' => (int) (clone $orders)->where('status', 'paid')->count(),
                'revenue' => (clone $orders)->where('status', 'paid')
                    ->selectRaw('currency, SUM(total_cents) as cents')->groupBy('currency')->pluck('cents', 'currency')
                    ->map(fn ($c) => round($c / 100, 2))->all(),
            ],
            'platform' => $this->platform->month($month),
            'funnel' => $funnel,
            'actions' => $actions,
        ];
    }
}
