<?php

namespace App\Filament\Resources\SummerClubExercises\Tables;

use App\Models\SummerClubSubscriptionRequest;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SummerClubExercisesTable
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

                Tables\Columns\TextColumn::make('resource.title')
                    ->label('Ressource liée')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable()
                    ->limit(35),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Matière')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('level')
                    ->label('Niveau')
                    ->formatStateUsing(fn (?string $state) => SummerClubSubscriptionRequest::levelOptions()[$state] ?? ($state ?: '-'))
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->badge()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Publié')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('subject')
                    ->label('Matière')
                    ->options(SummerClubSubscriptionRequest::subjectOptions()),

                SelectFilter::make('level')
                    ->label('Niveau')
                    ->options(SummerClubSubscriptionRequest::levelOptions()),

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
