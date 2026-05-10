<?php

namespace App\Filament\Resources\Exercises\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class ExercisesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->limit(45),

                Tables\Columns\TextColumn::make('course.title')
                    ->label('Cours')
                    ->placeholder('—')
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('packItem.title')
                    ->label('Item pack')
                    ->placeholder('—')
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('exercise_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'pdf'         => '📄 PDF',
                        'interactive' => '🎯 Interactif',
                        default       => '🎯 Interactif',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'pdf'   => 'warning',
                        default => 'info',
                    }),

                Tables\Columns\TextColumn::make('difficulty')
                    ->label('Difficulté')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'easy'   => 'Facile',
                        'medium' => 'Moyen',
                        'hard'   => 'Difficile',
                        default  => '—',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'easy'   => 'success',
                        'medium' => 'warning',
                        'hard'   => 'danger',
                        default  => 'gray',
                    }),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->hidden(fn ($record) => $record?->exercise_type === 'pdf'),

                Tables\Columns\TextColumn::make('estimated_duration_minutes')
                    ->label('Durée')
                    ->formatStateUsing(fn ($state) => $state ? $state . ' min' : '—')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('show_correction_immediately')
                    ->label('Correction imm.')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('allow_retry')
                    ->label('Retry')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\ToggleColumn::make('is_published')
                    ->label('Publié'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('exercise_type')
                    ->label('Type')
                    ->options([
                        'interactive' => '🎯 Interactif',
                        'pdf'         => '📄 PDF',
                    ]),

                Tables\Filters\SelectFilter::make('difficulty')
                    ->label('Difficulté')
                    ->options([
                        'easy'   => 'Facile',
                        'medium' => 'Moyen',
                        'hard'   => 'Difficile',
                    ]),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Publié'),

                Tables\Filters\TernaryFilter::make('allow_retry')
                    ->label('Retry autorisé'),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make()->label('Modifier'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('publish')
                        ->label('Publier la sélection')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->each->update(['is_published' => true])),

                    \Filament\Actions\BulkAction::make('unpublish')
                        ->label('Dépublier la sélection')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->each->update(['is_published' => false])),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
