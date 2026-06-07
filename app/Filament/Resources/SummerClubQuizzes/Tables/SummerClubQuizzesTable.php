<?php

namespace App\Filament\Resources\SummerClubQuizzes\Tables;

use App\Models\SummerClubSubscriptionRequest;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SummerClubQuizzesTable
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

                Tables\Columns\TextColumn::make('resource.title')
                    ->label('Ressource liée')
                    ->placeholder('-')
                    ->limit(35)
                    ->toggleable(),

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

                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Questions')
                    ->counts('questions')
                    ->sortable()
                    ->badge(),

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
