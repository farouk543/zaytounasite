<?php

namespace App\Filament\Resources\SummerClubExercises;

use App\Filament\Resources\SummerClubExercises\Pages\CreateSummerClubExercise;
use App\Filament\Resources\SummerClubExercises\Pages\EditSummerClubExercise;
use App\Filament\Resources\SummerClubExercises\Pages\ListSummerClubExercises;
use App\Filament\Resources\SummerClubExercises\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\SummerClubExercises\Schemas\SummerClubExerciseForm;
use App\Filament\Resources\SummerClubExercises\Tables\SummerClubExercisesTable;
use App\Models\SummerClubExercise;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SummerClubExerciseResource extends Resource
{
    protected static ?string $model = SummerClubExercise::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static UnitEnum|string|null $navigationGroup = 'Club d’été';

    protected static ?int $navigationSort = 21;

    protected static ?string $navigationLabel = 'Exercices interactifs';

    protected static ?string $modelLabel = 'Exercice interactif';

    protected static ?string $pluralModelLabel = 'Exercices interactifs';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return SummerClubExerciseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SummerClubExercisesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSummerClubExercises::route('/'),
            'create' => CreateSummerClubExercise::route('/create'),
            'edit' => EditSummerClubExercise::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('courses.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('courses.create') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('courses.update') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('courses.delete') ?? false;
    }
}
