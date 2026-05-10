<?php

namespace App\Filament\Resources\SummerClubEnrollments\Pages;

use App\Filament\Resources\SummerClubEnrollments\SummerClubEnrollmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSummerClubEnrollments extends ListRecords
{
    protected static string $resource = SummerClubEnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Créer un abonnement'),
        ];
    }
}
