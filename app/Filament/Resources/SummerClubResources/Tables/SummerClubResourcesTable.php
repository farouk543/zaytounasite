<?php

namespace App\Filament\Resources\SummerClubResources\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SummerClubResourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image_path')
                    ->label('Image')
                    ->disk('public')
                    ->visibility('public')
                    ->size(52)
                    ->square()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->limit(45),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'exercice' => 'Exercice',
                        'quiz' => 'Quiz',
                        'fiche' => 'Fiche de révision',
                        default => $state ?: '-',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'exercice' => 'success',
                        'quiz' => 'info',
                        'fiche' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Matière')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('level')
                    ->label('Niveau')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Publié')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_locked')
                    ->label('Verrouillé')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'exercice' => 'Exercice',
                        'quiz' => 'Quiz',
                        'fiche' => 'Fiche de révision',
                    ]),

                SelectFilter::make('subject')
                    ->label('Matière')
                    ->options([
                        'Français' => 'Français',
                        'Anglais' => 'Anglais',
                        'Mathématiques' => 'Mathématiques',
                    ]),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Publié'),
            ])
            ->defaultSort('sort_order')
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
