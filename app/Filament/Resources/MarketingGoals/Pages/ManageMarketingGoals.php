<?php

namespace App\Filament\Resources\MarketingGoals\Pages;

use App\Filament\Pages\SiteAnalytics;
use App\Filament\Resources\MarketingGoals\MarketingGoalResource;
use App\Models\MarketingGoal;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageMarketingGoals extends ManageRecords
{
    protected static string $resource = MarketingGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('score')
                ->label('Voir la note de la stratégie')
                ->icon('heroicon-o-presentation-chart-line')
                ->color('gray')
                ->url(SiteAnalytics::getUrl()),

            Action::make('nextMonth')
                ->label('Préparer le mois suivant')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->modalDescription('Copie les objectifs d’un mois vers le mois suivant en appliquant une progression. Les objectifs déjà fixés pour le mois suivant ne sont pas modifiés.')
                ->schema([
                    Forms\Components\Select::make('from')
                        ->label('Reprendre les objectifs de')
                        ->options(fn () => MarketingGoal::query()->distinct()->orderByDesc('month')->pluck('month')
                            ->mapWithKeys(fn ($m) => [$m => (new MarketingGoal(['month' => $m]))->monthLabel()])->all())
                        ->default(fn () => MarketingGoal::max('month'))
                        ->required(),
                    Forms\Components\TextInput::make('growth')
                        ->label('Progression')
                        ->numeric()
                        ->default(20)
                        ->suffix('%')
                        ->helperText('Les taux (%) ne sont pas augmentés.')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $next = CarbonImmutable::createFromFormat('Y-m-d', $data['from'].'-01')->addMonthNoOverflow()->format('Y-m');
                    $factor = 1 + ((float) $data['growth'] / 100);
                    $created = 0;

                    foreach (MarketingGoal::where('month', $data['from'])->get() as $goal) {
                        $new = MarketingGoal::firstOrCreate(
                            ['month' => $next, 'metric' => $goal->metric, 'country' => $goal->country],
                            ['target' => $goal->isRate() ? $goal->target : ceil($goal->target * $factor)],
                        );
                        $created += $new->wasRecentlyCreated ? 1 : 0;
                    }

                    Notification::make()->success()
                        ->title("{$created} objectif(s) créé(s) pour ".(new MarketingGoal(['month' => $next]))->monthLabel())
                        ->send();
                }),

            CreateAction::make()->label('Nouvel objectif'),
        ];
    }
}
