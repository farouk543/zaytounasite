<?php

namespace App\Filament\Resources\SummerClubResources\Pages;

use App\Filament\Resources\SummerClubResources\SummerClubResourceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSummerClubResource extends EditRecord
{
    protected static string $resource = SummerClubResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
