<?php

namespace App\Filament\Resources\Exercises;

use App\Filament\Resources\Exercises\Pages\CreateExercise;
use App\Filament\Resources\Exercises\Pages\EditExercise;
use App\Filament\Resources\Exercises\Pages\ListExercises;
use App\Filament\Resources\Exercises\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Exercises\Schemas\ExerciseForm;
use App\Filament\Resources\Exercises\Tables\ExercisesTable;
use App\Models\Exercise;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ExerciseResource extends Resource
{
    protected static ?string $model = Exercise::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel    = 'Exercices';
    protected static ?string $modelLabel         = 'Exercice';
    protected static ?string $pluralModelLabel   = 'Exercices';


    public static function form(Schema $schema): Schema
    {
        return ExerciseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExercisesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            \App\Filament\RelationManagers\MarketPricesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListExercises::route('/'),
            'create' => CreateExercise::route('/create'),
            'edit'   => EditExercise::route('/{record}/edit'),
        ];
    }

    // ── Permissions (réutilise la permission courses.*) ──────────────────

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
