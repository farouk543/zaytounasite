<?php

namespace App\Filament\Resources\LiveSubscriptions\Tables;

use App\Filament\Resources\LiveSubscriptions\LiveSubscriptionActions;
use App\Models\Order;
use App\Support\RegimeSubscription;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LiveSubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Reçue')
                    ->since()
                    ->dateTimeTooltip('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent_name')
                    ->label('Parent')
                    ->getStateUsing(fn (Order $record) => $record->parent_name ?: $record->user?->name)
                    ->description(fn (Order $record) => $record->phone)
                    ->searchable(['parent_name', 'phone', 'contact_email'])
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('student_name')
                    ->label('Élève')
                    ->placeholder('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('regime')
                    ->label('Programme')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state) => RegimeSubscription::regimeLabel($state)),

                Tables\Columns\TextColumn::make('pack')
                    ->label('Pack')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn (?string $state) => RegimeSubscription::packLabel($state))
                    ->description(fn (Order $record) => RegimeSubscription::planLabel($record->plan)),

                Tables\Columns\TextColumn::make('details')
                    ->label('Matière(s)')
                    ->getStateUsing(fn (Order $record) => RegimeSubscription::shortSummary($record))
                    ->tooltip(fn (Order $record) => implode("\n", RegimeSubscription::detailLines($record)))
                    ->wrap(),

                Tables\Columns\TextColumn::make('total_cents')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state, Order $record) => RegimeSubscription::totalLabel($record))
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => RegimeSubscription::statusLabel($state))
                    ->color(fn (?string $state) => match ($state) {
                        'pending_manual' => 'warning',
                        'paid'           => 'success',
                        'canceled', 'refunded' => 'danger',
                        default          => 'gray',
                    }),

                Tables\Columns\IconColumn::make('access_sent_at')
                    ->label('Accès envoyés')
                    ->getStateUsing(fn (Order $record) => $record->access_sent_at !== null)
                    ->boolean()
                    ->tooltip(fn (Order $record) => $record->access_sent_at?->format('d/m/Y H:i'))
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('regime')
                    ->label('Programme')
                    ->options(RegimeSubscription::REGIMES),

                SelectFilter::make('pack')
                    ->label('Pack')
                    ->options(RegimeSubscription::PACKS),
            ])
            ->recordActions([
                LiveSubscriptionActions::approve(),
                ActionGroup::make([
                    ViewAction::make()->label('Voir la fiche'),
                    LiveSubscriptionActions::resendAccess(),
                    LiveSubscriptionActions::reject(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucune demande')
            ->emptyStateDescription('Les demandes d\'abonnement envoyées depuis les pages « régimes » apparaîtront ici.');
    }
}
