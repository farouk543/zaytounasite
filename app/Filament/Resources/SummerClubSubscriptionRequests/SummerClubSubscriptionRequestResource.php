<?php

namespace App\Filament\Resources\SummerClubSubscriptionRequests;

use App\Filament\Resources\SummerClubSubscriptionRequests\Pages\ListSummerClubSubscriptionRequests;
use App\Filament\Resources\SummerClubSubscriptionRequests\Pages\EditSummerClubSubscriptionRequest;
use App\Filament\Resources\SummerClubSubscriptionRequests\Schemas\SummerClubSubscriptionRequestForm;
use App\Filament\Resources\SummerClubSubscriptionRequests\Tables\SummerClubSubscriptionRequestsTable;
use App\Models\SummerClubSubscriptionRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SummerClubSubscriptionRequestResource extends Resource
{
    protected static ?string $model = SummerClubSubscriptionRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static UnitEnum|string|null $navigationGroup = 'Club d’été';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Demandes d’abonnement';

    protected static ?string $modelLabel = 'Demande d’abonnement';

    protected static ?string $pluralModelLabel = 'Demandes d’abonnement';

    protected static ?string $recordTitleAttribute = 'student_name';

    public static function form(Schema $schema): Schema
    {
        return SummerClubSubscriptionRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SummerClubSubscriptionRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSummerClubSubscriptionRequests::route('/'),
            'edit' => EditSummerClubSubscriptionRequest::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['user', 'approvedBy', 'rejectedBy']);
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
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
}
