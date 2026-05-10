<?php

namespace App\Filament\Resources\SummerClubQuizzes;

use App\Filament\Resources\SummerClubQuizzes\Pages\CreateSummerClubQuiz;
use App\Filament\Resources\SummerClubQuizzes\Pages\EditSummerClubQuiz;
use App\Filament\Resources\SummerClubQuizzes\Pages\ListSummerClubQuizzes;
use App\Filament\Resources\SummerClubQuizzes\RelationManagers\QuestionsRelationManager;
use App\Filament\Resources\SummerClubQuizzes\Schemas\SummerClubQuizForm;
use App\Filament\Resources\SummerClubQuizzes\Tables\SummerClubQuizzesTable;
use App\Models\SummerClubQuiz;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SummerClubQuizResource extends Resource
{
    protected static ?string $model = SummerClubQuiz::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?int $navigationSort = 20;

    protected static UnitEnum|string|null $navigationGroup = 'Club d’été';

    protected static ?string $navigationLabel = 'Quiz interactifs';

    protected static ?string $modelLabel = 'Quiz interactif';

    protected static ?string $pluralModelLabel = 'Quiz interactifs';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return SummerClubQuizForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SummerClubQuizzesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSummerClubQuizzes::route('/'),
            'create' => CreateSummerClubQuiz::route('/create'),
            'edit' => EditSummerClubQuiz::route('/{record}/edit'),
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
