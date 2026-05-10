<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminKpiOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    protected function getStats(): array
    {
        $currency = 'TND';

        $paidTotalCents = (int) Order::query()
            ->where('status', 'paid')
            ->sum('total_cents');

        $paidOrders = (int) Order::query()
            ->where('status', 'paid')
            ->count();

        $pendingOrders = (int) Order::query()
            ->where('status', 'pending')
            ->count();

        $pendingManualOrders = (int) Order::query()
            ->where('status', 'pending_manual')
            ->count();

        $newUsers7d = (int) User::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $newUsers30d = (int) User::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $totalStudents = (int) User::query()->count();

        $activeEnrollments = (int) Enrollment::query()->where('status', 'active')->count();
        $newEnrollments7d  = (int) Enrollment::query()
            ->where('enrolled_at', '>=', now()->subDays(7))
            ->count();

        $publishedCourses   = (int) Course::query()->where('is_published', true)->count();
        $unpublishedCourses = (int) Course::query()->where('is_published', false)->count();

        $fmtMoney = fn (int $cents) => number_format($cents / 100, 2) . " {$currency}";

        return [
            Stat::make("Chiffre d'affaires", $fmtMoney($paidTotalCents))
                ->description("{$paidOrders} commande(s) payée(s)")
                ->color('success'),

            Stat::make("Inscriptions actives", $activeEnrollments)
                ->description("+{$newEnrollments7d} cette semaine")
                ->color('success'),

            Stat::make("Validation manuelle", $pendingManualOrders)
                ->description($pendingManualOrders > 0 ? 'En attente d\'approbation admin' : 'Aucune demande en attente')
                ->color($pendingManualOrders > 0 ? 'warning' : 'gray'),

            Stat::make("Commandes en attente", $pendingOrders)
                ->description("A verifier / relancer")
                ->color($pendingOrders > 0 ? 'warning' : 'gray'),

            Stat::make("Cours publies", $publishedCourses)
                ->description("{$unpublishedCourses} en brouillon")
                ->color('info'),

            Stat::make("Utilisateurs total", $totalStudents)
                ->description("{$newUsers7d} nouveaux (7j) — {$newUsers30d} (30j)")
                ->color('gray'),
        ];
    }
}