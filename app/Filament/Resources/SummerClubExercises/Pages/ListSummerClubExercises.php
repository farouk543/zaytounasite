<?php

namespace App\Filament\Resources\SummerClubExercises\Pages;

use App\Filament\Resources\SummerClubExercises\SummerClubExerciseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSummerClubExercises extends ListRecords
{
    protected static string $resource = SummerClubExerciseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Créer un exercice'),
        ];
    }
}
