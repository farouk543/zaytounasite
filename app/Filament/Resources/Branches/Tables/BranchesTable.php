<?php

namespace App\Filament\Resources\Branches\Tables;

use App\Models\Track;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
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

                Tables\Columns\TextColumn::make('level.name')
                    ->label('Level')
                    ->sortable(),

                Tables\Columns\TextColumn::make('track.name')
                    ->label('Track')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('track')
                    ->label('Track')
                    ->options(fn () => Track::query()->orderBy('sort_order')->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        $trackId = $data['value'] ?? null;

                        if (! $trackId) {
                            return $query;
                        }

                        return $query->where('track_id', $trackId);
                    }),

                Tables\Filters\TernaryFilter::make('is_active')->label('Actif'),
            ])
            ->defaultSort('sort_order', 'asc');
    }
}