<?php

namespace App\Filament\Resources\Quizzes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class QuizzesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('course.title')
                    ->label('Course')
                    ->placeholder('-')
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('packItem.title')
                    ->label('Item pack')
                    ->placeholder('-')
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Questions')
                    ->counts('questions')
                    ->sortable(),

                Tables\Columns\TextColumn::make('passing_score')
                    ->label('Réussite')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('time_limit_minutes')
                    ->label('Temps')
                    ->formatStateUsing(fn ($state) => $state ? $state . ' min' : '-')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_randomized')
                    ->label('Aléatoire')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('show_result_immediately')
                    ->label('Résultat direct')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\ToggleColumn::make('is_published')
                    ->label('Publié'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Publié'),

                Tables\Filters\TernaryFilter::make('is_randomized')
                    ->label('Aléatoire'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make()->label('Modifier'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}