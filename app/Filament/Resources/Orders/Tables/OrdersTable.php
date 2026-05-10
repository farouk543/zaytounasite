<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\OrderApproved;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        $fmt = fn (?int $cents, $record) =>
            number_format((($cents ?? 0) / 100), 2) . ' ' . ($record->currency ?? 'TND');

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Commande #')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (?string $s) => match ($s) {
                        'pending'         => 'En attente',
                        'pending_manual'  => 'Validation manuelle',
                        'paid'            => 'Payée',
                        'failed'          => 'Échouée',
                        'refunded'        => 'Remboursée',
                        'canceled'        => 'Annulée',
                        default           => $s ?? '—',
                    })
                    ->color(fn (?string $s) => match ($s) {
                        'paid'            => 'success',
                        'pending'         => 'warning',
                        'pending_manual'  => 'warning',
                        'failed'          => 'danger',
                        'refunded'        => 'gray',
                        'canceled'        => 'gray',
                        default           => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_cents')
                    ->label('Total')
                    ->alignEnd()
                    ->formatStateUsing($fmt)
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
                        'pending'        => 'En attente',
                        'pending_manual' => 'Validation manuelle',
                        'paid'           => 'Payée',
                        'failed'         => 'Échouée',
                        'refunded'       => 'Remboursée',
                        'canceled'       => 'Annulée',
                    ]),

                SelectFilter::make('user_id')
                    ->label('Utilisateur')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending_manual')
                    ->requiresConfirmation()
                    ->modalHeading('Approuver cette commande ?')
                    ->modalDescription('L\'accès aux cours sera activé immédiatement pour l\'utilisateur.')
                    ->modalSubmitActionLabel('Oui, approuver')
                    ->action(function ($record) {
                        DB::transaction(function () use ($record) {
                            $record->load('items.course', 'user');
                            $isRegime = $record->payments()->where('provider', 'regime')->exists();

                            if (! $isRegime) {
                                // Catalogue : activer les inscriptions
                                foreach ($record->items as $item) {
                                    if (! $item->course) continue;
                                    Enrollment::updateOrCreate(
                                        ['user_id' => $record->user_id, 'course_id' => $item->course_id],
                                        ['enrolled_at' => now(), 'access_ends_at' => null, 'status' => 'active']
                                    );
                                }
                                $record->payments()->where('provider', 'manual')->update(['status' => 'paid']);
                            } else {
                                // Régime : juste marquer le paiement comme accepté
                                $record->payments()->where('provider', 'regime')->update(['status' => 'paid']);
                            }

                            $record->update(['status' => 'paid']);
                        });

                        // Notifier l'utilisateur
                        $record->load('user');
                        $record->user?->notify(new OrderApproved($record));

                        FilamentNotification::make()
                            ->title('Commande approuvée')
                            ->body('Les inscriptions ont été activées et l\'utilisateur est notifié.')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending_manual')
                    ->requiresConfirmation()
                    ->modalHeading('Rejeter cette commande ?')
                    ->modalDescription('La commande sera annulée et aucun accès ne sera accordé.')
                    ->modalSubmitActionLabel('Oui, rejeter')
                    ->action(function ($record) {
                        $record->payments()->where('provider', 'manual')->update(['status' => 'failed']);
                        $record->update(['status' => 'canceled']);

                        FilamentNotification::make()
                            ->title('Commande rejetée')
                            ->body('La commande a été annulée.')
                            ->danger()
                            ->send();
                    }),

                EditAction::make()
                    ->label('Détails'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(null)
            ->bulkActions([]);
    }
}