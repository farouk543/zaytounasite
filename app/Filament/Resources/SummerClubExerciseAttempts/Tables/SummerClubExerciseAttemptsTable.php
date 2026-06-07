<?php

namespace App\Filament\Resources\SummerClubExerciseAttempts\Tables;

use App\Models\SummerClubSubscriptionRequest;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SummerClubExerciseAttemptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Eleve')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('exercise.title')
                    ->label('Exercice')
                    ->searchable()
                    ->sortable()
                    ->limit(45),

                Tables\Columns\TextColumn::make('exercise.subject')
                    ->label('Matiere')
                    ->formatStateUsing(fn ($state, $record) => $state ?: ($record->exercise?->resource?->subject ?: '-'))
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('exercise.level')
                    ->label('Niveau')
                    ->formatStateUsing(function (?string $state, $record) {
                        $level = $state ?: $record->exercise?->resource?->level;

                        return SummerClubSubscriptionRequest::levelOptions()[$level] ?? ($level ?: '-');
                    })
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->formatStateUsing(fn ($state, $record) => "{$state} / {$record->total} points")
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total points')
                    ->sortable(),

                Tables\Columns\TextColumn::make('percentage')
                    ->label('%')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\IconColumn::make('passed')
                    ->label('Reussi')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Termine le')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Eleve')
                    ->options(fn () => User::query()->orderBy('name')->pluck('email', 'id')->all())
                    ->searchable(),

                SelectFilter::make('subject')
                    ->label('Matiere')
                    ->options(SummerClubSubscriptionRequest::subjectOptions())
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null)
                        ? $query->whereHas('exercise', fn (Builder $query) => $query->where(function (Builder $query) use ($data) {
                            $query->where('subject', $data['value'])
                                ->orWhere(function (Builder $query) use ($data) {
                                    $query->where(function (Builder $query) {
                                        $query->whereNull('subject')->orWhere('subject', '');
                                    })->whereHas('resource', fn (Builder $query) => $query->where('subject', $data['value']));
                                });
                        }))
                        : $query),

                SelectFilter::make('level')
                    ->label('Niveau')
                    ->options(SummerClubSubscriptionRequest::levelOptions())
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null)
                        ? $query->whereHas('exercise', fn (Builder $query) => $query->where(function (Builder $query) use ($data) {
                            $query->where('level', $data['value'])
                                ->orWhere(function (Builder $query) use ($data) {
                                    $query->where(function (Builder $query) {
                                        $query->whereNull('level')->orWhere('level', '');
                                    })->whereHas('resource', fn (Builder $query) => $query->where('level', $data['value']));
                                });
                        }))
                        : $query),

                Tables\Filters\TernaryFilter::make('passed')
                    ->label('Reussi'),

                Filter::make('completed_at')
                    ->label('Date')
                    ->form([
                        DatePicker::make('from')->label('Du'),
                        DatePicker::make('until')->label('Au'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('completed_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date) => $query->whereDate('completed_at', '<=', $date))),
            ])
            ->defaultSort('completed_at', 'desc');
    }
}
