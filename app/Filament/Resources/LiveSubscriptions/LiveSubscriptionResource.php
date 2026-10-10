<?php

namespace App\Filament\Resources\LiveSubscriptions;

use App\Filament\Resources\LiveSubscriptions\Pages\ListLiveSubscriptions;
use App\Filament\Resources\LiveSubscriptions\Pages\ViewLiveSubscription;
use App\Filament\Resources\LiveSubscriptions\Schemas\LiveSubscriptionInfolist;
use App\Filament\Resources\LiveSubscriptions\Tables\LiveSubscriptionsTable;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Demandes d'abonnement aux cours live (régimes Tunisie, France, Qatar…) :
 * qui veut s'abonner, à quel pack, et l'envoi des accès plateforme au parent.
 */
class LiveSubscriptionResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $slug = 'abonnements-live';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;
    protected static string|\UnitEnum|null $navigationGroup = 'Ventes & Paiements';
    protected static ?int $navigationSort = -1;
    protected static ?string $navigationLabel = 'Abonnements live';
    protected static ?string $modelLabel = 'abonnement live';
    protected static ?string $pluralModelLabel = 'Abonnements live';
    protected static ?string $recordTitleAttribute = 'id';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', 'regime')
            ->with('user');
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::query()
            ->where('type', 'regime')
            ->where('status', 'pending_manual')
            ->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Demandes à traiter';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit(Model $record): bool { return false; }
    public static function canDelete(Model $record): bool { return false; }

    public static function infolist(Schema $schema): Schema
    {
        return LiveSubscriptionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LiveSubscriptionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLiveSubscriptions::route('/'),
            'view'  => ViewLiveSubscription::route('/{record}'),
        ];
    }
}
