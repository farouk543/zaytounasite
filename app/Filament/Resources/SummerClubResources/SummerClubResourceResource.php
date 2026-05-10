<?php

namespace App\Filament\Resources\SummerClubResources;

use App\Filament\Resources\SummerClubResources\Pages\CreateSummerClubResource;
use App\Filament\Resources\SummerClubResources\Pages\EditSummerClubResource;
use App\Filament\Resources\SummerClubResources\Pages\ListSummerClubResources;
use App\Filament\Resources\SummerClubResources\Schemas\SummerClubResourceForm;
use App\Filament\Resources\SummerClubResources\Tables\SummerClubResourcesTable;
use App\Models\SummerClubResource;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SummerClubResourceResource extends Resource
{
    protected static ?string $model = SummerClubResource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?int $navigationSort = 10;

    protected static UnitEnum|string|null $navigationGroup = 'Club d’été';

    protected static ?string $navigationLabel = 'Club d’été — Contenu pédagogique';

    protected static ?string $modelLabel = 'Ressource Club d’été';

    protected static ?string $pluralModelLabel = 'Contenu pédagogique Club d’été';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return SummerClubResourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SummerClubResourcesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSummerClubResources::route('/'),
            'create' => CreateSummerClubResource::route('/create'),
            'edit' => EditSummerClubResource::route('/{record}/edit'),
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
