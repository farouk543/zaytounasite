<?php

namespace App\Filament\Resources\Enrollments\Tables;

use App\Filament\Resources\Enrollments\EnrollmentResource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('course.title')
                    ->label('Cours')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'active'   => 'Actif',
                        'canceled' => 'Suspendu',
                        'expired'  => 'Expire',
                        default    => $state ?? '-',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'active'   => 'success',
                        'canceled' => 'danger',
                        'expired'  => 'warning',
                        default    => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('enrolled_at')
                    ->label('Achete le')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('access_ends_at')
                    ->label('Fin acces')
                    ->dateTime('Y-m-d')
                    ->placeholder('Illimite')
                    ->sortable()
                    ->color(fn ($record) => $record->access_ends_at
                        && $record->access_ends_at->lt(now()->addDays(7))
                        ? 'danger'
                        : null),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active'   => 'Actif',
                        'canceled' => 'Suspendu',
                        'expired'  => 'Expire',
                    ]),

                Filter::make('expiring_soon')
                    ->label('Expire dans 7 jours')
                    ->query(fn (Builder $q) => $q
                        ->whereNotNull('access_ends_at')
                        ->where('access_ends_at', '<=', now()->addDays(7))
                        ->where('access_ends_at', '>=', now())
                    )
                    ->toggle(),

                Filter::make('no_expiry')
                    ->label('Acces illimite')
                    ->query(fn (Builder $q) => $q->whereNull('access_ends_at'))
                    ->toggle(),

                SelectFilter::make('course_id')
                    ->label('Cours')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('user_id')
                    ->label('Utilisateur')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('enrolled_at', 'desc')
            ->recordUrl(fn ($record) => EnrollmentResource::getUrl('edit', ['record' => $record]))
            ->recordAction(null)
            ->actions([])
            ->bulkActions([]);
    }
}
