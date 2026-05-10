<?php

namespace App\Filament\Resources\Subjects\Tables;

use App\Models\Branch;
use App\Models\Track;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubjectsTable
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
                    ->label('Niveau')
                    ->sortable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branche')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('level.track.name')
                    ->label('Filière')
                    ->sortable()
                    ->toggleable(),

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
                    ->label('Filière')
                    ->options(fn () => Track::query()->orderBy('sort_order')->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        $trackId = $data['value'] ?? null;

                        if (! $trackId) {
                            return $query;
                        }

                        return $query->whereHas(
                            'level.track',
                            fn (Builder $q) => $q->where('id', $trackId)
                        );
                    }),

                Tables\Filters\SelectFilter::make('level_id')
                    ->label('Niveau')
                    ->relationship('level', 'name'),

                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Branche')
                    ->options(fn () => Branch::query()->orderBy('sort_order')->pluck('name', 'id')),

                Tables\Filters\TernaryFilter::make('is_active')->label('Actif'),
            ])
            ->defaultSort('sort_order', 'asc');
    }
}