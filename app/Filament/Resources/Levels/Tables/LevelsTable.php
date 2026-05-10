<?php

namespace App\Filament\Resources\Levels\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class LevelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('branches'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom (FR)')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('track.name')
                    ->label('Track')
                    ->sortable(),

                Tables\Columns\TextColumn::make('branches_count')
                    ->label('Branches')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state) => ($state ?? 0) > 0 ? 'info' : 'gray'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('Y-m-d')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('track_id')
                    ->label('Track')
                    ->relationship('track', 'name'),

                Tables\Filters\TernaryFilter::make('is_active')->label('Actif'),
            ])
            ->defaultSort('sort_order', 'asc');
    }
}