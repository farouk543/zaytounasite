<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\Enrollment;
use App\Models\Course;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';
    protected static ?string $title = 'Achats & Accès';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course.title')
                    ->label('Cours')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'active' => 'Actif',
                        'canceled' => 'Suspendu',
                        'expired' => 'Expiré',
                        default => $state ?? '—',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'active' => 'success',
                        'canceled' => 'danger',
                        'expired' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('enrolled_at')
                    ->label('Acheté le')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('access_ends_at')
                    ->label("Fin d’accès")
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('Illimité')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                // ✅ Remplace EditAction (qui n'existe pas chez toi)
                Action::make('editEnrollment')
                    ->label('Modifier')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'active' => 'Actif',
                                'canceled' => 'Suspendu',
                                'expired' => 'Expiré',
                            ])
                            ->required(),

                        Forms\Components\DateTimePicker::make('access_ends_at')
                            ->label("Fin d'accès (optionnel)")
                            ->helperText("Laisser vide pour un accès illimité.")
                            ->seconds(false)
                            ->nullable(),
                    ])
                    ->fillForm(function (Model $record): array {
                        return [
                            'status' => $record->status ?? 'active',
                            'access_ends_at' => $record->access_ends_at,
                        ];
                    })
                    ->action(function (Model $record, array $data): void {
                        $record->update([
                            'status' => $data['status'],
                            'access_ends_at' => $data['access_ends_at'] ?: null,
                        ]);
                    }),

                Action::make('suspend')
                    ->label('Suspendre')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Enrollment $record) => ($record->status ?? 'active') === 'active')
                    ->action(fn (Enrollment $record) => $record->update(['status' => 'canceled'])),

                Action::make('reactivate')
                    ->label('Réactiver')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Enrollment $record) => ($record->status ?? 'active') !== 'active')
                    ->action(fn (Enrollment $record) => $record->update([
                        'status' => 'active',
                        'access_ends_at' => null,
                    ])),
            ])
            ->headerActions([
                Action::make('grantAccess')
                    ->label('Donner accès à un cours')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('course_id')
                            ->label('Cours')
                            ->options(fn () => Course::query()
                                ->orderBy('title')
                                ->pluck('title', 'id')
                                ->all())
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'active' => 'Actif',
                                'canceled' => 'Suspendu',
                                'expired' => 'Expiré',
                            ])
                            ->default('active')
                            ->required(),

                        Forms\Components\DateTimePicker::make('access_ends_at')
                            ->label("Fin d'accès (optionnel)")
                            ->helperText("Laisser vide pour un accès illimité.")
                            ->seconds(false)
                            ->nullable(),
                    ])
                    ->action(function (array $data): void {
                        /** @var \App\Models\User $user */
                        $user = $this->getOwnerRecord();

                        Enrollment::updateOrCreate(
                            ['user_id' => $user->id, 'course_id' => (int) $data['course_id']],
                            [
                                'status' => $data['status'] ?? 'active',
                                'access_ends_at' => $data['access_ends_at'] ?: null,
                                'enrolled_at' => now(),
                            ]
                        );

                        $this->notify('success', 'Accès accordé au cours.');
                    }),
            ])
            ->bulkActions([]);
    }
}