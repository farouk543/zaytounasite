<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rôle')
                    ->badge()
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : ($state ?? '—'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')

            // ✅ clic sur la ligne = ouvrir la page edit
            ->recordUrl(fn ($record) => UserResource::getUrl('edit', ['record' => $record]))
            ->recordAction(null)

            // ✅ actions par ligne
            ->recordActions([
                EditAction::make()->label('Modifier'),

                DeleteAction::make()
                    ->label('Supprimer')
                    ->requiresConfirmation()
                    ->visible(function (User $record): bool {
                        $me = auth()->user();
                        if (!($me?->hasRole('admin') ?? false)) return false;

                        // ne pas supprimer soi-même
                        if ((int) $record->id === (int) $me->id) return false;

                        // ne pas supprimer le dernier admin
                        if ($record->hasRole('admin')) {
                            $adminsCount = User::role('admin')->count();
                            if ($adminsCount <= 1) return false;
                        }

                        return true;
                    }),
            ])

            // ✅ actions bulk
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Supprimer sélection')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()?->hasRole('admin') ?? false)
                        ->before(function ($records) {
                            $me = auth()->user();

                            // ne pas supprimer soi-même
                            if ($me && $records->contains(fn ($u) => (int) $u->id === (int) $me->id)) {
                                throw new \Exception("Vous ne pouvez pas supprimer votre propre compte.");
                            }

                            // ne pas supprimer le dernier admin
                            $selectedAdmins = $records->filter(fn ($u) => $u->hasRole('admin'))->count();
                            if ($selectedAdmins > 0) {
                                $adminsCount = User::role('admin')->count();
                                if (($adminsCount - $selectedAdmins) < 1) {
                                    throw new \Exception("Impossible : vous essayez de supprimer le dernier admin.");
                                }
                            }
                        }),
                ]),
            ]);
    }
}