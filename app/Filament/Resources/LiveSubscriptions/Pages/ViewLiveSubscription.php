<?php

namespace App\Filament\Resources\LiveSubscriptions\Pages;

use App\Filament\Resources\LiveSubscriptions\LiveSubscriptionActions;
use App\Filament\Resources\LiveSubscriptions\LiveSubscriptionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewLiveSubscription extends ViewRecord
{
    protected static string $resource = LiveSubscriptionResource::class;

    public function getTitle(): string
    {
        return 'Abonnement #' . $this->record->id;
    }

    protected function getHeaderActions(): array
    {
        return [
            LiveSubscriptionActions::approve(),
            LiveSubscriptionActions::reject(),
            LiveSubscriptionActions::resendAccess(),
        ];
    }
}
