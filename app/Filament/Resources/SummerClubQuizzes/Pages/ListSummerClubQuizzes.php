<?php

namespace App\Filament\Resources\SummerClubQuizzes\Pages;

use App\Filament\Resources\SummerClubQuizzes\SummerClubQuizResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSummerClubQuizzes extends ListRecords
{
    protected static string $resource = SummerClubQuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Créer un quiz'),
        ];
    }
}
