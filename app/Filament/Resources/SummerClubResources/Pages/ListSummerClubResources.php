<?php

namespace App\Filament\Resources\SummerClubResources\Pages;

use App\Filament\Resources\SummerClubResources\SummerClubResourceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSummerClubResources extends ListRecords
{
    protected static string $resource = SummerClubResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Créer une ressource'),
        ];
    }
}
