<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SummerClubSubscriptionRequest;
use App\Models\Testimonial;
use App\Services\Analytics\AnalyticsReport;
use App\Services\Analytics\GoalTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Statistiques AGRÉGÉES du site vitrine pour l'admin de la plateforme d'enseignement
 * (page « Site principal ») : audience, pays, sources, pages, clics, ventes, objectifs.
 * Aucune donnée personnelle : ni nom, ni e-mail, ni IP, ni empreinte de visiteur.
 *
 *   GET /api/site/stats?period=30d&month=2026-11   (Authorization: Bearer <PLATFORM_STATS_TOKEN>)
 *
 * Même jeton partagé que l'API inverse (plateforme → site) : PLATFORM_STATS_TOKEN = MARKETING_API_TOKEN.
 */
class SiteStatsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $token = (string) config('zaytouna.platform_stats_token');

        // API fermée tant qu'aucun jeton n'est configuré ; comparaison en temps constant.
        abort_if($token === '', 404);
        abort_unless(hash_equals($token, (string) $request->bearerToken()), 401);

        $period = (string) $request->query('period', '30d');
        abort_unless(array_key_exists($period, AnalyticsReport::PERIODS), 422);

        $month = (string) $request->query('month', now(config('marketing.timezone'))->format('Y-m'));
        abort_unless(preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) === 1, 422);

        $data = Cache::remember("site-stats:$period:$month", now()->addMinutes(5), fn () => $this->stats($period, $month));

        return response()->json($data, 200, ['Cache-Control' => 'private, no-store']);
    }

    /** @return array<string, mixed> */
    public function stats(string $period, string $month): array
    {
        $report = new AnalyticsReport($period);
        $goals = app(GoalTracker::class);

        $orders = Order::query()->whereBetween('created_at', [$report->from, $report->to]);

        return [
            'period' => $period,
            'period_label' => $report->label(),
            'periods' => AnalyticsReport::PERIODS,
            'from' => $report->from->toIso8601String(),
            'to' => $report->to->toIso8601String(),
            'generated_at' => now()->toIso8601String(),
            'live_visitors' => $report->liveVisitors(),
            'kpis' => $report->kpis(),
            'timeline' => $report->timeline(),
            'markets' => [
                ...($markets = $report->markets()),
                'rows' => $this->rows($markets['rows'], ['code', 'name', 'visitors', 'share', 'delta', 'google', 'blog', 'signups']),
            ],
            'sources' => $this->rows($report->sources(), ['key', 'visitors', 'percent']),
            'devices' => $this->rows($report->breakdown('device', 5), ['key', 'visitors', 'percent']),
            'campaigns' => $this->rows($report->breakdown('utm_campaign', 8), ['key', 'visitors', 'views']),
            'top_pages' => $this->rows($report->topPages(10), ['path', 'title', 'views', 'visitors']),
            'entry_pages' => $this->rows($report->entryPages(8), ['path', 'title', 'entries']),
            'blog_posts' => $this->rows($report->blogPosts(8), ['path', 'title', 'views', 'visitors']),
            'top_clicks' => $this->rows($report->topClicks(10), ['label', 'kind', 'clicks', 'visitors']),
            'monthly' => $this->rows($report->monthly(12), ['month', 'visitors', 'pageviews', 'clicks', 'signups', 'orders', 'paid', 'growth']),
            'sales' => [
                'by_status' => (clone $orders)->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status'),
                'by_type' => (clone $orders)->selectRaw("COALESCE(NULLIF(regime, ''), type, 'autre') as k, COUNT(*) as c")
                    ->groupBy('k')->orderByDesc('c')->pluck('c', 'k'),
                'paid_by_currency' => (clone $orders)->where('status', 'paid')
                    ->selectRaw('currency, COUNT(*) as orders, SUM(total_cents) as total_cents')
                    ->groupBy('currency')->get()
                    ->map(fn ($r) => ['currency' => $r->currency, 'orders' => (int) $r->orders, 'total' => round($r->total_cents / 100, 2)]),
                'pending_manual' => Order::where('status', 'pending_manual')->count(),
            ],
            'leads' => [
                'testimonials_pending' => Testimonial::where('is_published', false)->count(),
                'testimonials_published' => Testimonial::where('is_published', true)->count(),
                'club_requests' => SummerClubSubscriptionRequest::whereBetween('created_at', [$report->from, $report->to])->count(),
            ],
            'goals' => [
                'month' => $month,
                'elapsed' => round($goals->elapsed($month), 2),
                'score' => $goals->score($month),
                'rows' => $this->rows($goals->progress($month), ['label', 'scope', 'target', 'actual', 'projected', 'percent', 'status', 'color', 'unit']),
                'tips' => $goals->recommendations($month),
                'history' => $this->rows($goals->history(), ['month', 'label', 'score', 'verdict', 'color']),
            ],
        ];
    }

    /** Ne garde que les champs listés (liste blanche : rien d'autre ne sort du site). */
    private function rows(Collection $rows, array $fields): array
    {
        return $rows->map(function ($row) use ($fields) {
            $row = is_array($row) ? $row : (method_exists($row, 'getAttributes') ? $row->getAttributes() : (array) $row);

            return collect($fields)->mapWithKeys(fn ($f) => [$f => $row[$f] ?? null])->all();
        })->values()->all();
    }
}
