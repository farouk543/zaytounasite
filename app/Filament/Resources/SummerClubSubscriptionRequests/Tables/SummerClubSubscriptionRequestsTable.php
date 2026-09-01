<?php

namespace App\Filament\Resources\SummerClubSubscriptionRequests\Tables;

use App\Models\SummerClubEnrollment;
use App\Models\SummerClubSubscriptionRequest;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SummerClubSubscriptionRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('parent_name')
                    ->label('Parent')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('student_name')
                    ->label('Élève')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('pack_name')
                    ->label('Pack')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('selected_subjects')
                    ->label('Matières')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : ($state ?: '-'))
                    ->badge()
                    ->separator(','),

                Tables\Columns\TextColumn::make('price')
                    ->label('Prix')
                    ->formatStateUsing(fn ($state, $record) => \App\Services\CurrencyService::format((float) $state, $record->currency ?? 'TND')
                        . ($record->currency && $record->currency !== 'TND' && $record->base_price
                            ? ' (' . \App\Services\CurrencyService::format((float) $record->base_price, 'TND') . ')'
                            : ''))
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'pending' => 'En attente',
                        'approved' => 'Approuvée',
                        'rejected' => 'Rejetée',
                        'canceled' => 'Annulée',
                        default => $state ?: '-',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected', 'canceled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'approved' => 'Approuvée',
                        'rejected' => 'Rejetée',
                        'canceled' => 'Annulée',
                    ]),

                SelectFilter::make('pack_key')
                    ->label('Pack')
                    ->options(SummerClubSubscriptionRequest::packOptions()),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form(fn ($record) => [
                        Forms\Components\Select::make('user_id')
                            ->label('Utilisateur étudiant')
                            ->options(fn () => User::query()->orderBy('name')->pluck('email', 'id'))
                            ->default($record->user_id)
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('level')
                            ->label('Niveau autorisé')
                            ->options(SummerClubSubscriptionRequest::levelOptions())
                            ->in(array_keys(SummerClubSubscriptionRequest::levelOptions()))
                            ->searchable()
                            ->nullable()
                            ->helperText('Recommandé pour limiter l’accès au niveau précis de l’étudiant.'),
                    ])
                    ->requiresConfirmation()
                    ->action(function ($record, array $data) {
                        $userId = (int) $data['user_id'];
                        $subjects = SummerClubSubscriptionRequest::subjectsForPack(
                            $record->pack_key,
                            $record->selected_subjects ?? []
                        );

                        $record->update([
                            'user_id' => $userId,
                            'selected_subjects' => $subjects,
                            'status' => 'approved',
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                        ]);

                        SummerClubEnrollment::create([
                            'user_id' => $userId,
                            'pack_key' => $record->pack_key,
                            'pack_name' => SummerClubSubscriptionRequest::packDefinitions()[$record->pack_key]['name'] ?? $record->pack_name,
                            'selected_subjects' => $subjects,
                            'level' => SummerClubSubscriptionRequest::normalizeLevel($data['level'] ?? null),
                            'status' => 'active',
                            'starts_at' => now(),
                            'expires_at' => now()->addMonths(3),
                            'confirmed_at' => now(),
                            'confirmed_by' => auth()->id(),
                            'notes' => "Créé depuis la demande d'abonnement #{$record->id}",
                        ]);
                    }),

                Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Notes admin')
                            ->rows(4),
                    ])
                    ->requiresConfirmation()
                    ->action(fn ($record, array $data) => $record->update([
                        'status' => 'rejected',
                        'rejected_at' => now(),
                        'rejected_by' => auth()->id(),
                        'admin_notes' => $data['admin_notes'] ?? $record->admin_notes,
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
