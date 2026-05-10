<?php

namespace App\Filament\Resources\SummerClubExercises\Pages;

use App\Filament\Resources\SummerClubExercises\SummerClubExerciseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSummerClubExercise extends EditRecord
{
    protected static string $resource = SummerClubExerciseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
