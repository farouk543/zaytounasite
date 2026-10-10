<?php

namespace App\Filament\Resources\LiveSubscriptions\Pages;

use App\Filament\Resources\LiveSubscriptions\LiveSubscriptionResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLiveSubscriptions extends ListRecords
{
    protected static string $resource = LiveSubscriptionResource::class;

    public function getTabs(): array
    {
        $count = fn (string $status) => LiveSubscriptionResource::getEloquentQuery()->where('status', $status)->count();

        return [
            'pending' => Tab::make('À traiter')
                ->badge($count('pending_manual'))
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'pending_manual')),

            'accepted' => Tab::make('Acceptées')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'paid')),

            'refused' => Tab::make('Refusées')
                ->modifyQueryUsing(fn (Builder $q) => $q->whereIn('status', ['canceled', 'refunded'])),

            'all' => Tab::make('Toutes'),
        ];
    }
}
