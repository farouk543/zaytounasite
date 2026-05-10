<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Filament\Resources\Payments\PaymentResource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_id')
                    ->label('Commande #')
                    ->sortable(),

                Tables\Columns\TextColumn::make('provider')
                    ->label('Provider')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (?string $s) => match ($s) {
                        'initiated' => 'Initié',
                        'pending' => 'En attente',
                        'paid' => 'Payé',
                        'failed' => 'Échoué',
                        'refunded' => 'Remboursé',
                        default => $s ?? '—',
                    })
                    ->color(fn (?string $s) => match ($s) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction')
                    ->copyable()
                    ->limit(28)
                    ->toggleable(),

                // ✅ lien direct cliquable (sans Action)
                Tables\Columns\TextColumn::make('payment_url')
                    ->label('Lien paiement')
                    ->limit(28)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->url(fn ($record) => $record->payment_url ?: null, shouldOpenInNewTab: true)
                    ->formatStateUsing(fn ($state) => $state ? 'Ouvrir' : '—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'initiated' => 'Initié',
                        'pending' => 'En attente',
                        'paid' => 'Payé',
                        'failed' => 'Échoué',
                        'refunded' => 'Remboursé',
                    ]),

                SelectFilter::make('provider')
                    ->label('Provider')
                    ->options([
                        'mock' => 'mock',
                        'manual' => 'manual',
                        'konnect' => 'konnect',
                        'flouci' => 'flouci',
                        'paymee' => 'paymee',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            // ✅ clic sur la ligne -> détails
            ->recordUrl(fn ($record) => PaymentResource::getUrl('edit', ['record' => $record]))
            ->recordAction(null)
            ->bulkActions([]);
    }
}