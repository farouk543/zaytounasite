<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ExerciseAttemptsRelationManager extends RelationManager
{
    protected static string $relationship = 'exerciseAttempts';

    protected static ?string $title = 'Tentatives Exercices';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('started_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('exercise.title')
                    ->label('Exercice')
                    ->limit(40)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state, $record) => $state . ' / ' . $record->max_score),

                Tables\Columns\TextColumn::make('percentage')
                    ->label('%')
                    ->alignCenter()
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->color(fn ($state) => $state >= 70 ? 'success' : ($state >= 50 ? 'warning' : 'danger')),

                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Terminé')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
