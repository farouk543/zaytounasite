<?php

namespace App\Filament\Resources\SummerClubEnrollments\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SummerClubEnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Élève')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('pack_name')
                    ->label('Pack')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('selected_subjects')
                    ->label('Matières')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : ($state ?: '-'))
                    ->badge()
                    ->separator(',')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'pending' => 'En attente',
                        'active' => 'Actif',
                        'canceled' => 'Annulé',
                        'expired' => 'Expiré',
                        default => $state ?: '-',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'canceled' => 'danger',
                        'expired' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Début')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expiration')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('Illimité')
                    ->sortable(),

                Tables\Columns\TextColumn::make('confirmed_at')
                    ->label('Confirmé le')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'active' => 'Actif',
                        'canceled' => 'Annulé',
                        'expired' => 'Expiré',
                    ]),

                SelectFilter::make('pack_name')
                    ->label('Pack')
                    ->options(fn () => \App\Models\SummerClubEnrollment::query()
                        ->whereNotNull('pack_name')
                        ->orderBy('pack_name')
                        ->pluck('pack_name', 'pack_name')),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('activate')
                    ->label('Confirmer / Activer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== 'active')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update([
                        'status' => 'active',
                        'starts_at' => now(),
                        'confirmed_at' => now(),
                        'confirmed_by' => auth()->id(),
                    ])),

                Action::make('cancel')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status !== 'canceled')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update([
                        'status' => 'canceled',
                    ])),

                EditAction::make()->label('Modifier'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
