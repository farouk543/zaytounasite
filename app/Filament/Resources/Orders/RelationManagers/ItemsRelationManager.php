<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Articles';

    public function table(Table $table): Table
    {
        $fmt = fn (?int $cents, $record) =>
            number_format((($cents ?? 0) / 100), 2) . ' ' . ($record->order?->currency ?? 'TND');

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course.title')
                    ->label('Cours')
                    ->searchable()
                    ->limit(60),

                Tables\Columns\TextColumn::make('qty')
                    ->label('Qté')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('unit_price_cents')
                    ->label('Prix unité')
                    ->formatStateUsing($fmt)
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('base_price_cents')
                    ->label('Prix de base')
                    ->formatStateUsing(fn (?int $cents, $record) => $cents === null
                        ? '—'
                        : number_format($cents / 100, 2) . ' ' . ($record->base_currency ?? '—'))
                    ->alignEnd()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ajouté le')
                    ->dateTime('Y-m-d H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([])
            ->headerActions([])
            ->bulkActions([]);
    }
}