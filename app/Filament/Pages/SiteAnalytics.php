<?php

namespace App\Filament\Pages;

use App\Models\MarketingGoal;
use App\Services\Analytics\AnalyticsReport;
use App\Services\Analytics\GoalTracker;
use App\Services\Analytics\PlatformStats;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Url;
use UnitEnum;

/**
 * Statistiques de fréquentation : visiteurs, pages, sources, pays, clics, évolution mensuelle.
 * Données collectées par le middleware TrackPageView et le script de clics (sans cookie).
 */
class SiteAnalytics extends Page
{
    protected string $view = 'filament.pages.site-analytics';

    protected static ?string $slug = 'statistiques';

    protected static ?string $navigationLabel = 'Statistiques du site';

    protected static ?string $title = 'Statistiques du site';

    protected static string|UnitEnum|null $navigationGroup = 'Marketing & Blog';

    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static ?int $navigationSort = 1;

    #[Url]
    public string $period = '30d';

    #[Url]
    public string $goalMonth = '';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function setGoalMonth(string $month): void
    {
        $this->goalMonth = preg_match('/^\d{4}-\d{2}$/', $month) ? $month : '';
    }

    public function setPeriod(string $period): void
    {
        $this->period = array_key_exists($period, AnalyticsReport::PERIODS) ? $period : '30d';
    }

    protected function getViewData(): array
    {
        $report = new AnalyticsReport(array_key_exists($this->period, AnalyticsReport::PERIODS) ? $this->period : '30d');

        $goals = app(GoalTracker::class);
        $goalMonth = $this->goalMonth ?: now(config('marketing.timezone'))->format('Y-m');

        return [
            'goalMonth' => $goalMonth,
            'goalMonths' => MarketingGoal::monthOptions(3, 2),
            'goalRows' => $goals->progress($goalMonth),
            'goalScore' => $goals->score($goalMonth),
            'goalTips' => $goals->recommendations($goalMonth),
            'goalElapsed' => $goals->elapsed($goalMonth),
            'goalHistory' => $goals->history(),
            'markets' => $report->markets(),
            'platform' => app(PlatformStats::class)->month($goalMonth),
            'platformMonth' => ucfirst(\Carbon\CarbonImmutable::createFromFormat('Y-m-d', $goalMonth.'-01')->locale('fr')->isoFormat('MMMM YYYY')),
            'platformConfigured' => app(PlatformStats::class)->configured(),
            'report' => $report,
            'periods' => AnalyticsReport::PERIODS,
            'live' => $report->liveVisitors(),
            'kpis' => $report->kpis(),
            'timeline' => $report->timeline(),
            'sources' => $report->sources(),
            'countries' => $report->breakdown('country', 12),
            'referrers' => $report->breakdown('referrer_host', 10, excludeInternal: true),
            'devices' => $report->breakdown('device', 5),
            'browsers' => $report->breakdown('browser', 8),
            'systems' => $report->breakdown('os', 8),
            'locales' => $report->breakdown('locale', 5),
            'campaigns' => $report->breakdown('utm_campaign', 10),
            'topPages' => $report->topPages(),
            'leastPages' => $report->leastVisitedPages(),
            'entryPages' => $report->entryPages(),
            'blogPosts' => $report->blogPosts(),
            'readDepth' => $report->blogReadDepth(),
            'topClicks' => $report->topClicks(),
            'clicksByPage' => $report->clicksByPage(),
            'outbound' => $report->outboundClicks(),
            'hours' => $report->hours(),
            'monthly' => $report->monthly(),
        ];
    }
}
