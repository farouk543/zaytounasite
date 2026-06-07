<?php

namespace App\Filament\Resources\SummerClubEnrollments\Tables;

use App\Models\SummerClubSubscriptionRequest;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

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

                Tables\Columns\TextColumn::make('level')
                    ->label('Niveau')
                    ->formatStateUsing(fn (?string $state) => SummerClubSubscriptionRequest::levelOptions()[$state] ?? ($state ?: '-'))
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => SummerClubSubscriptionRequest::statusOptions()[$state] ?? ($state ?: '-'))
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
                    ->options(SummerClubSubscriptionRequest::statusOptions()),

                SelectFilter::make('pack_key')
                    ->label('Pack')
                    ->options(SummerClubSubscriptionRequest::packOptions()),

                SelectFilter::make('level')
                    ->label('Niveau')
                    ->options(SummerClubSubscriptionRequest::levelOptions()),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('activate')
                    ->label('Activer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== 'active')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        try {
                            $data = SummerClubSubscriptionRequest::normalizeEnrollmentData([
                                'pack_key' => $record->pack_key,
                                'pack_name' => $record->pack_name,
                                'selected_subjects' => $record->selected_subjects ?? [],
                                'status' => 'active',
                                'starts_at' => $record->starts_at,
                                'expires_at' => $record->expires_at,
                                'confirmed_at' => $record->confirmed_at,
                                'confirmed_by' => $record->confirmed_by,
                            ]);
                        } catch (ValidationException) {
                            FilamentNotification::make()
                                ->title('Impossible d’activer cet accès')
                                ->body('Les matières autorisées sont manquantes ou incohérentes, ou le pack est invalide.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->update([
                            'pack_name' => $data['pack_name'],
                            'selected_subjects' => $data['selected_subjects'],
                            'status' => 'active',
                            'starts_at' => $data['starts_at'],
                            'expires_at' => $data['expires_at'],
                            'confirmed_at' => $data['confirmed_at'],
                            'confirmed_by' => $data['confirmed_by'],
                        ]);
                    }),

                Action::make('cancel')
                    ->label('Fermer l’accès')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status !== 'canceled')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update([
                        'status' => 'canceled',
                    ])),

                Action::make('expire')
                    ->label('Marquer expiré')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->visible(fn ($record) => $record->status !== 'expired')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update([
                        'status' => 'expired',
                        'expires_at' => $record->expires_at && $record->expires_at->isPast()
                            ? $record->expires_at
                            : now(),
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
