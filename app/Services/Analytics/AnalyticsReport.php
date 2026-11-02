<?php

namespace App\Services\Analytics;

use App\Models\ClickEvent;
use App\Models\Order;
use App\Models\PageView;
use App\Models\User;
use App\Services\SitemapBuilder;
use App\Support\Markets;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Calcule toutes les statistiques de la page « Statistiques du site » pour une période.
 */
class AnalyticsReport
{
    public const PERIODS = [
        'today' => "Aujourd'hui",
        '7d' => '7 derniers jours',
        '30d' => '30 derniers jours',
        'month' => 'Ce mois-ci',
        'last_month' => 'Mois dernier',
        '90d' => '3 derniers mois',
        '12m' => '12 derniers mois',
    ];

    public readonly CarbonImmutable $from;

    public readonly CarbonImmutable $to;

    public function __construct(public readonly string $period = '30d')
    {
        $now = CarbonImmutable::now();

        [$from, $to] = match ($period) {
            'today' => [$now->startOfDay(), $now->endOfDay()],
            '7d' => [$now->subDays(6)->startOfDay(), $now->endOfDay()],
            'month' => [$now->startOfMonth(), $now->endOfDay()],
            'last_month' => [$now->subMonthNoOverflow()->startOfMonth(), $now->subMonthNoOverflow()->endOfMonth()],
            '90d' => [$now->subDays(89)->startOfDay(), $now->endOfDay()],
            '12m' => [$now->subMonthsNoOverflow(11)->startOfMonth(), $now->endOfDay()],
            default => [$now->subDays(29)->startOfDay(), $now->endOfDay()],
        };

        $this->from = $from;
        $this->to = $to;
    }

    public function label(): string
    {
        return self::PERIODS[$this->period] ?? self::PERIODS['30d'];
    }

    /** Période précédente de même durée (pour les comparaisons +/- %). */
    private function previousRange(): array
    {
        if ($this->period === 'month') {
            $prevStart = $this->from->subMonthNoOverflow();

            return [$prevStart, $prevStart->addDays($this->from->diffInDays($this->to))];
        }
        if ($this->period === 'last_month') {
            $prev = $this->from->subMonthNoOverflow();

            return [$prev->startOfMonth(), $prev->endOfMonth()];
        }

        $seconds = $this->to->getTimestamp() - $this->from->getTimestamp();

        return [$this->from->subSeconds($seconds + 1), $this->from->subSecond()];
    }

    private function views(?CarbonImmutable $from = null, ?CarbonImmutable $to = null): Builder
    {
        return PageView::query()->whereBetween('created_at', [$from ?? $this->from, $to ?? $this->to]);
    }

    private function clicks(): Builder
    {
        return ClickEvent::query()->whereBetween('created_at', [$this->from, $this->to]);
    }

    // ───────────────────────── KPIs ─────────────────────────

    public function kpis(): array
    {
        [$pFrom, $pTo] = $this->previousRange();

        $current = $this->kpiValues($this->from, $this->to);
        $previous = $this->kpiValues($pFrom, $pTo);

        $out = [];
        foreach ($current as $key => $value) {
            $prev = $previous[$key];
            $out[$key] = [
                'value' => $value,
                'previous' => $prev,
                'delta' => $prev > 0 ? round(($value - $prev) / $prev * 100) : null,
            ];
        }

        return $out;
    }

    private function kpiValues(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $visitors = (int) $this->views($from, $to)->distinct()->count('visitor_hash');
        $pageviews = (int) $this->views($from, $to)->count();
        $signups = (int) User::whereBetween('created_at', [$from, $to])->count();
        $orders = (int) Order::whereBetween('created_at', [$from, $to])->count();
        $paid = (int) Order::whereBetween('created_at', [$from, $to])->where('status', 'paid')->count();

        // Visiteurs n'ayant vu qu'une seule page (taux de rebond approximatif).
        $single = (int) DB::query()
            ->fromSub($this->views($from, $to)->select('visitor_hash')->groupBy('visitor_hash')->havingRaw('COUNT(*) = 1'), 's')
            ->count();

        return [
            'visitors' => $visitors,
            'pageviews' => $pageviews,
            'pages_per_visitor' => $visitors > 0 ? round($pageviews / $visitors, 1) : 0,
            'bounce' => $visitors > 0 ? (int) round($single / $visitors * 100) : 0,
            'signups' => $signups,
            'orders' => $orders,
            'paid' => $paid,
            'conversion' => $visitors > 0 ? round($signups / $visitors * 100, 1) : 0,
        ];
    }

    public function liveVisitors(): int
    {
        return (int) PageView::where('created_at', '>=', now()->subMinutes(5))->distinct()->count('visitor_hash');
    }

    // ───────────────────────── Courbe ─────────────────────────

    /** @return array{unit: string, rows: Collection<int, array{key: string, label: string, visitors: int, pageviews: int}>} */
    public function timeline(): array
    {
        $byMonth = $this->from->diffInDays($this->to) > 92;
        $expr = $byMonth ? $this->monthExpr() : $this->dayExpr();

        $data = $this->views()
            ->selectRaw("$expr as k, COUNT(*) as pv, COUNT(DISTINCT visitor_hash) as v")
            ->groupBy('k')
            ->get()
            ->keyBy('k');

        $rows = collect();
        if ($byMonth) {
            for ($m = $this->from->startOfMonth(); $m <= $this->to; $m = $m->addMonthNoOverflow()) {
                $k = $m->format('Y-m');
                $rows->push(['key' => $k, 'label' => $m->locale('fr')->isoFormat('MMM YY'),
                    'visitors' => (int) ($data[$k]->v ?? 0), 'pageviews' => (int) ($data[$k]->pv ?? 0)]);
            }
        } else {
            foreach (CarbonPeriod::create($this->from->startOfDay(), $this->to->startOfDay()) as $day) {
                $k = $day->format('Y-m-d');
                $rows->push(['key' => $k, 'label' => $day->locale('fr')->isoFormat('ddd D MMM'),
                    'visitors' => (int) ($data[$k]->v ?? 0), 'pageviews' => (int) ($data[$k]->pv ?? 0)]);
            }
        }

        return ['unit' => $byMonth ? 'mois' : 'jour', 'rows' => $rows];
    }

    // ───────────────────────── Pages ─────────────────────────

    public function topPages(int $limit = 15): Collection
    {
        return $this->views()
            ->selectRaw('path, MAX(title) as title, COUNT(*) as views, COUNT(DISTINCT visitor_hash) as visitors')
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();
    }

    /** Pages publiques du site (sitemap) les moins vues sur la période, y compris celles jamais vues. */
    public function leastVisitedPages(int $limit = 15): Collection
    {
        $counts = $this->views()
            ->selectRaw('path, COUNT(*) as views')
            ->groupBy('path')
            ->pluck('views', 'path');

        // Les vues stockent le chemin relatif à l'application ; si le site tourne dans un
        // sous-dossier (ex. /zaytouna/public en local), on retire ce préfixe des URL du sitemap.
        $basePath = rtrim((string) parse_url(url('/'), PHP_URL_PATH), '/');

        return app(SitemapBuilder::class)->entries()
            ->map(function (array $entry) use ($counts, $basePath) {
                $path = (string) parse_url($entry['loc'], PHP_URL_PATH);
                if ($basePath !== '' && str_starts_with($path, $basePath)) {
                    $path = substr($path, strlen($basePath));
                }
                $path = '/'.ltrim($path, '/');

                return (object) ['path' => $path, 'title' => $entry['label'], 'views' => (int) ($counts[$path] ?? 0)];
            })
            ->unique('path')
            ->sortBy('views')
            ->take($limit)
            ->values();
    }

    public function entryPages(int $limit = 10): Collection
    {
        // Première page vue par chaque visiteur sur la période = page d'arrivée.
        $firstIds = $this->views()->selectRaw('MIN(id) as id')->groupBy('visitor_hash');

        return PageView::query()
            ->whereIn('id', $firstIds)
            ->selectRaw('path, MAX(title) as title, COUNT(*) as entries')
            ->groupBy('path')
            ->orderByDesc('entries')
            ->limit($limit)
            ->get();
    }

    public function blogPosts(int $limit = 10): Collection
    {
        return $this->views()
            ->where('route_name', 'blog.show')
            ->selectRaw('path, MAX(title) as title, COUNT(*) as views, COUNT(DISTINCT visitor_hash) as visitors')
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();
    }

    // ───────────────────────── Audience ─────────────────────────

    /** Répartition des visiteurs uniques selon une colonne (source, pays, appareil…). */
    public function breakdown(string $column, int $limit = 10, bool $excludeInternal = false): Collection
    {
        abort_unless(in_array($column, ['source', 'country', 'device', 'browser', 'os', 'locale', 'referrer_host', 'utm_campaign'], true), 400);

        $rows = $this->views()
            ->when($excludeInternal, fn ($q) => $q->where('source', '!=', 'Interne'))
            ->whereNotNull($column)
            ->selectRaw("$column as k, COUNT(DISTINCT visitor_hash) as visitors, COUNT(*) as views")
            ->groupBy('k')
            ->orderByDesc('visitors')
            ->limit($limit)
            ->get();

        $total = max(1, (int) $rows->sum('visitors'));

        return $rows->map(fn ($r) => (object) [
            'key' => $r->k,
            'visitors' => (int) $r->visitors,
            'views' => (int) $r->views,
            'percent' => round($r->visitors / $total * 100),
        ]);
    }

    /**
     * Nos 5 marchés prioritaires (config/marketing.php) + « autres pays » et « non identifié ».
     *
     * @return array{rows: Collection, priority_share: int, total: int}
     */
    public function markets(): array
    {
        [$pFrom, $pTo] = $this->previousRange();
        $codes = array_keys(Markets::all());

        $count = fn (CarbonImmutable $from, CarbonImmutable $to) => $this->views($from, $to)
            ->selectRaw('country as k, COUNT(DISTINCT visitor_hash) as v')->groupBy('k')->pluck('v', 'k');

        $current = $count($this->from, $this->to);
        $previous = $count($pFrom, $pTo);
        $google = $this->views()->where('source', 'Google')
            ->selectRaw('country as k, COUNT(DISTINCT visitor_hash) as v')->groupBy('k')->pluck('v', 'k');
        $blog = $this->views()->where('route_name', 'blog.show')
            ->selectRaw('country as k, COUNT(DISTINCT visitor_hash) as v')->groupBy('k')->pluck('v', 'k');
        $signups = PageView::query()
            ->whereIn('user_id', User::whereBetween('created_at', [$this->from, $this->to])->select('id'))
            ->selectRaw('country as k, COUNT(DISTINCT user_id) as v')->groupBy('k')->pluck('v', 'k');

        $total = max(1, (int) $this->views()->distinct()->count('visitor_hash'));

        $rows = collect($codes)->map(fn ($cc) => (object) [
            'code' => $cc,
            'name' => Markets::name($cc, 'fr'),
            'flag' => Markets::flag($cc),
            'visitors' => (int) ($current[$cc] ?? 0),
            'share' => (int) round(($current[$cc] ?? 0) / $total * 100),
            'delta' => ($previous[$cc] ?? 0) > 0 ? (int) round((($current[$cc] ?? 0) - $previous[$cc]) / $previous[$cc] * 100) : null,
            'google' => (int) ($google[$cc] ?? 0),
            'blog' => (int) ($blog[$cc] ?? 0),
            'signups' => (int) ($signups[$cc] ?? 0),
        ]);

        $sumOthers = fn ($set) => (int) collect($set)->except($codes)->filter(fn ($v, $k) => $k !== '' && $k !== null)->sum();
        $others = $sumOthers($current);
        $unknown = (int) ($current[''] ?? 0);

        $rows->push((object) ['code' => 'XX', 'name' => 'Autres pays', 'flag' => '🌍', 'visitors' => $others,
            'share' => (int) round($others / $total * 100), 'delta' => null,
            'google' => $sumOthers($google), 'blog' => $sumOthers($blog), 'signups' => $sumOthers($signups)]);

        $prioritySum = $rows->whereIn('code', $codes)->sum('visitors');

        return [
            'rows' => $rows,
            'priority_share' => (int) round($prioritySum / $total * 100),
            'unknown' => $unknown,
            'total' => $total,
        ];
    }

    /** Source d'acquisition : la source de la 1re page vue de chaque visiteur. */
    public function sources(int $limit = 10): Collection
    {
        $firstIds = $this->views()->selectRaw('MIN(id) as id')->groupBy('visitor_hash');

        $rows = PageView::query()
            ->whereIn('id', $firstIds)
            ->selectRaw("CASE WHEN source = 'Interne' THEN 'Direct' ELSE source END as k, COUNT(*) as visitors")
            ->groupBy('k')
            ->orderByDesc('visitors')
            ->limit($limit)
            ->get();

        $total = max(1, (int) $rows->sum('visitors'));

        return $rows->map(fn ($r) => (object) [
            'key' => $r->k,
            'visitors' => (int) $r->visitors,
            'percent' => round($r->visitors / $total * 100),
        ]);
    }

    public function hours(): Collection
    {
        $expr = match (DB::connection()->getDriverName()) {
            'sqlite' => "CAST(strftime('%H', created_at) AS INTEGER)",
            'pgsql' => 'EXTRACT(HOUR FROM created_at)::int',
            default => 'HOUR(created_at)',
        };

        $data = $this->views()->selectRaw("$expr as h, COUNT(*) as views")->groupBy('h')->pluck('views', 'h');

        return collect(range(0, 23))->map(fn ($h) => (object) ['hour' => $h, 'views' => (int) ($data[$h] ?? 0)]);
    }

    // ───────────────────────── Clics ─────────────────────────

    public function topClicks(int $limit = 20): Collection
    {
        return $this->clicks()
            ->where('label', 'not like', 'Lecture article %')
            ->selectRaw('label, kind, MAX(target) as target, COUNT(*) as clicks, COUNT(DISTINCT visitor_hash) as visitors')
            ->groupBy('label', 'kind')
            ->orderByDesc('clicks')
            ->limit($limit)
            ->get();
    }

    public function clicksByPage(int $limit = 10): Collection
    {
        return $this->clicks()
            ->where('label', 'not like', 'Lecture article %')
            ->selectRaw('page_path, COUNT(*) as clicks')
            ->groupBy('page_path')
            ->orderByDesc('clicks')
            ->limit($limit)
            ->get();
    }

    public function outboundClicks(int $limit = 10): Collection
    {
        return $this->clicks()
            ->where('kind', 'external')
            ->selectRaw('target, MAX(label) as label, COUNT(*) as clicks')
            ->groupBy('target')
            ->orderByDesc('clicks')
            ->limit($limit)
            ->get();
    }

    /** Profondeur de lecture des articles (25/50/75/100 %). */
    public function blogReadDepth(): Collection
    {
        return $this->clicks()
            ->where('label', 'like', 'Lecture article %')
            ->selectRaw('label, COUNT(DISTINCT visitor_hash) as readers')
            ->groupBy('label')
            ->pluck('readers', 'label');
    }

    // ───────────────────────── Évolution mensuelle ─────────────────────────

    /** Les 12 derniers mois, indépendamment du filtre — le suivi « chaque mois ». */
    public function monthly(int $months = 12): Collection
    {
        $start = CarbonImmutable::now()->startOfMonth()->subMonthsNoOverflow($months - 1);
        $expr = $this->monthExpr();

        $views = PageView::where('created_at', '>=', $start)
            ->selectRaw("$expr as m, COUNT(*) as pv, COUNT(DISTINCT visitor_hash) as v")
            ->groupBy('m')->get()->keyBy('m');
        $signups = User::where('created_at', '>=', $start)
            ->selectRaw("$expr as m, COUNT(*) as c")->groupBy('m')->pluck('c', 'm');
        $orders = Order::where('created_at', '>=', $start)
            ->selectRaw("$expr as m, COUNT(*) as c, SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as p")
            ->groupBy('m')->get()->keyBy('m');
        $clicks = ClickEvent::where('created_at', '>=', $start)
            ->selectRaw("$expr as m, COUNT(*) as c")->groupBy('m')->pluck('c', 'm');

        $rows = collect();
        $prevVisitors = null;
        for ($m = $start; $m <= CarbonImmutable::now(); $m = $m->addMonthNoOverflow()) {
            $k = $m->format('Y-m');
            $visitors = (int) ($views[$k]->v ?? 0);
            $rows->push((object) [
                'month' => ucfirst($m->locale('fr')->isoFormat('MMMM YYYY')),
                'visitors' => $visitors,
                'pageviews' => (int) ($views[$k]->pv ?? 0),
                'clicks' => (int) ($clicks[$k] ?? 0),
                'signups' => (int) ($signups[$k] ?? 0),
                'orders' => (int) ($orders[$k]->c ?? 0),
                'paid' => (int) ($orders[$k]->p ?? 0),
                'growth' => $prevVisitors ? round(($visitors - $prevVisitors) / $prevVisitors * 100) : null,
            ]);
            $prevVisitors = $visitors;
        }

        return $rows->reverse()->values();
    }

    // ───────────────────────── SQL helpers ─────────────────────────

    private function dayExpr(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => 'date(created_at)',
            'pgsql' => "to_char(created_at, 'YYYY-MM-DD')",
            default => 'DATE(created_at)',
        };
    }

    private function monthExpr(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', created_at)",
            'pgsql' => "to_char(created_at, 'YYYY-MM')",
            default => "DATE_FORMAT(created_at, '%Y-%m')",
        };
    }
}
