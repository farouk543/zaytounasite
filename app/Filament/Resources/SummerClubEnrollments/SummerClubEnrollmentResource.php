<?php

namespace App\Filament\Resources\SummerClubEnrollments;

use App\Filament\Resources\SummerClubEnrollments\Pages\CreateSummerClubEnrollment;
use App\Filament\Resources\SummerClubEnrollments\Pages\EditSummerClubEnrollment;
use App\Filament\Resources\SummerClubEnrollments\Pages\ListSummerClubEnrollments;
use App\Filament\Resources\SummerClubEnrollments\Schemas\SummerClubEnrollmentForm;
use App\Filament\Resources\SummerClubEnrollments\Tables\SummerClubEnrollmentsTable;
use App\Models\SummerClubEnrollment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SummerClubEnrollmentResource extends Resource
{
    protected static ?string $model = SummerClubEnrollment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLockClosed;

    protected static UnitEnum|string|null $navigationGroup = 'Club d’été';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Abonnements Club d’été';

    protected static ?string $modelLabel = 'Abonnement Club d’été';

    protected static ?string $pluralModelLabel = 'Abonnements Club d’été';

    protected static ?string $recordTitleAttribute = 'pack_name';

    public static function form(Schema $schema): Schema
    {
        return SummerClubEnrollmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SummerClubEnrollmentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSummerClubEnrollments::route('/'),
            'create' => CreateSummerClubEnrollment::route('/create'),
            'edit' => EditSummerClubEnrollment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['user', 'confirmedBy']);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
}
