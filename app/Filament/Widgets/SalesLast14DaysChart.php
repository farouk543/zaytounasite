<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class SalesLast14DaysChart extends ChartWidget
{
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function getHeading(): string
    {
        return 'Ventes (14 derniers jours)';
    }

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 13; $i >= 0; $i--) {
            $day = now()->subDays($i)->startOfDay();
            $next = (clone $day)->addDay();

            $sum = (int) Order::query()
                ->where('status', 'paid')
                ->where('created_at', '>=', $day)
                ->where('created_at', '<', $next)
                ->sum('total_cents');

            $labels[] = $day->format('d/m');
            $data[] = round($sum / 100, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ventes (TND)',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}