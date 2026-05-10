<?php

namespace App\Filament\Resources\Courses\Tables;

use App\Models\Branch;
use App\Models\Track;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('enrollments'))
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_path')
                    ->label('Thumb')
                    ->disk('public')
                    ->visibility('public')
                    ->size(48)
                    ->square()
                    ->extraImgAttributes([
                        'class' => 'object-cover bg-gray-50',
                        'loading' => 'lazy',
                    ])
                    ->url(fn ($record) => filled($record->thumbnail_path)
                        ? Storage::disk('public')->url($record->thumbnail_path)
                        : null)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('delivery_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'course' => 'Cours',
                        'series' => 'Série',
                        'exam' => 'Examen',
                        'pack' => 'Pack course',
                        default => '-',
                    })
                    ->colors([
                        'success' => fn (?string $state) => $state === 'course',
                        'info' => fn (?string $state) => $state === 'series',
                        'danger' => fn (?string $state) => $state === 'exam',
                        'warning' => fn (?string $state) => $state === 'pack',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Matière')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('subject.branch.name')
                    ->label('Branche')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('subject.level.name')
                    ->label('Niveau')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('subject.level.track.name')
                    ->label('Filière')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\ToggleColumn::make('is_published')
                    ->label('Publié')
                    ->sortable()
                    ->disabled(fn () => !(auth()->user()?->can('courses.publish') ?? false)),

                Tables\Columns\TextColumn::make('price_cents')
                    ->label('Prix')
                    ->sortable()
                    ->formatStateUsing(fn (?int $state, $record) => ($record->is_paid ?? true)
                        ? number_format(($state ?? 0) / 100, 2) . ' ' . ($record->currency ?? 'TND')
                        : 'Gratuit')
                    ->alignEnd()
                    ->visible(fn () => auth()->user()?->can('courses.price') ?? false),

                Tables\Columns\TextColumn::make('enrollments_count')
                    ->label('Inscrits')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn (int $state) => $state > 0 ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('difficulty_level')
                    ->label('Niveau')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'beginner'     => 'Debutant',
                        'intermediate' => 'Intermediaire',
                        'advanced'     => 'Avance',
                        default        => '—',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'beginner'     => 'success',
                        'intermediate' => 'warning',
                        'advanced'     => 'danger',
                        default        => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duree')
                    ->formatStateUsing(fn (?int $state) => $state
                        ? (intdiv($state, 60) > 0
                            ? intdiv($state, 60) . 'h' . ($state % 60 > 0 ? ' ' . ($state % 60) . 'min' : '')
                            : $state . 'min')
                        : '—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Vedette')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('track')
                    ->label('Filière')
                    ->options(fn () => Track::query()->orderBy('sort_order')->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        $trackId = $data['value'] ?? null;

                        if (! $trackId) {
                            return $query;
                        }

                        return $query->whereHas(
                            'subject.level.track',
                            fn (Builder $q) => $q->where('id', $trackId)
                        );
                    }),

                SelectFilter::make('branch')
                    ->label('Branche')
                    ->options(fn () => Branch::query()->orderBy('sort_order')->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        $branchId = $data['value'] ?? null;

                        if (! $branchId) {
                            return $query;
                        }

                        return $query->whereHas(
                            'subject.branch',
                            fn (Builder $q) => $q->where('id', $branchId)
                        );
                    }),

                SelectFilter::make('delivery_type')
                    ->label('Type')
                    ->options([
                        'course' => 'Cours',
                        'series' => 'Série',
                        'exam' => 'Examen',
                        'pack' => 'Pack course',
                    ]),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Publié'),

                Tables\Filters\TernaryFilter::make('is_paid')
                    ->label('Payant'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Mis en avant'),

                SelectFilter::make('difficulty_level')
                    ->label('Difficulte')
                    ->options([
                        'beginner'     => 'Debutant',
                        'intermediate' => 'Intermediaire',
                        'advanced'     => 'Avance',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make()
                    ->label('Modifier'),

                EditAction::make('editPrice')
                    ->label('Prix')
                    ->visible(fn ($record) =>
                        ($record->is_paid ?? true) && (auth()->user()?->can('courses.price') ?? false)
                    )
                    ->form([
                        TextInput::make('price_cents')
                            ->label('Prix')
                            ->numeric()
                            ->prefix('DT')
                            ->step(0.01)
                            ->helperText('Exemple : 19.90 pour 19,90 DT')
                            ->required(),

                        TextInput::make('currency')
                            ->label('Devise')
                            ->maxLength(3)
                            ->default('TND')
                            ->required(),
                    ])
                    ->mutateRecordDataUsing(fn ($record) => [
                        'price_cents' => $record->price_cents ? number_format($record->price_cents / 100, 2, '.', '') : '0.00',
                        'currency' => (string) ($record->currency ?? 'TND'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'price_cents' => (int) round((float) $data['price_cents'] * 100),
                            'currency' => strtoupper((string) $data['currency']),
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('publish')
                        ->label('Publier la sélection')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_published' => true]))
                        ->visible(fn () => auth()->user()?->can('courses.publish') ?? false),

                    \Filament\Actions\BulkAction::make('unpublish')
                        ->label('Dépublier la sélection')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_published' => false]))
                        ->visible(fn () => auth()->user()?->can('courses.publish') ?? false),

                    \Filament\Actions\BulkAction::make('feature')
                        ->label('Mettre en vedette')
                        ->icon('heroicon-o-star')
                        ->color('info')
                        ->action(fn (Collection $records) => $records->each->update(['is_featured' => true])),

                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('courses.delete') ?? false),
                ]),
            ]);
    }
}