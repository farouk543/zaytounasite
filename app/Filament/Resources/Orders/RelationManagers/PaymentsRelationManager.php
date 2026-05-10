<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = 'Paiements';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('provider')
                    ->label('Provider')
                    ->badge(),

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
                    }),

                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction')
                    ->copyable()
                    ->limit(24),

                // ✅ lien direct cliquable (zéro Action)
                Tables\Columns\TextColumn::make('payment_url')
                    ->label('Lien paiement')
                    ->formatStateUsing(fn ($state) => $state ? 'Ouvrir' : '—')
                    ->url(fn ($record) => $record->payment_url ?: null, shouldOpenInNewTab: true)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->actions([])        // ✅ rien
            ->headerActions([])  // ✅ rien
            ->bulkActions([]);   // ✅ rien
    }
}