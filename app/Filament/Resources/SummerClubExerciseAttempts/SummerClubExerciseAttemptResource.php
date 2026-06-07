<?php

namespace App\Filament\Resources\SummerClubExerciseAttempts;

use App\Filament\Resources\SummerClubExerciseAttempts\Pages\ListSummerClubExerciseAttempts;
use App\Filament\Resources\SummerClubExerciseAttempts\Tables\SummerClubExerciseAttemptsTable;
use App\Models\SummerClubExerciseAttempt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SummerClubExerciseAttemptResource extends Resource
{
    protected static ?string $model = SummerClubExerciseAttempt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static UnitEnum|string|null $navigationGroup = 'Club d’été';

    protected static ?int $navigationSort = 61;

    protected static ?string $navigationLabel = 'Resultats exercices';

    protected static ?string $modelLabel = 'Resultat exercice';

    protected static ?string $pluralModelLabel = 'Resultats exercices';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return SummerClubExerciseAttemptsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSummerClubExerciseAttempts::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'exercise.resource', 'enrollment']);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
