<?php

namespace App\Filament\Resources\SummerClubQuizAttempts;

use App\Filament\Resources\SummerClubQuizAttempts\Pages\ListSummerClubQuizAttempts;
use App\Filament\Resources\SummerClubQuizAttempts\Tables\SummerClubQuizAttemptsTable;
use App\Models\SummerClubQuizAttempt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SummerClubQuizAttemptResource extends Resource
{
    protected static ?string $model = SummerClubQuizAttempt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static UnitEnum|string|null $navigationGroup = 'Club d’été';

    protected static ?int $navigationSort = 60;

    protected static ?string $navigationLabel = 'Resultats quiz';

    protected static ?string $modelLabel = 'Resultat quiz';

    protected static ?string $pluralModelLabel = 'Resultats quiz';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return SummerClubQuizAttemptsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSummerClubQuizAttempts::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'quiz.resource', 'enrollment']);
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
