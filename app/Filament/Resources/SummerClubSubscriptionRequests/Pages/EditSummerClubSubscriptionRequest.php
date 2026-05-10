<?php

namespace App\Filament\Resources\SummerClubSubscriptionRequests\Pages;

use App\Filament\Resources\SummerClubSubscriptionRequests\SummerClubSubscriptionRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSummerClubSubscriptionRequest extends EditRecord
{
    protected static string $resource = SummerClubSubscriptionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
