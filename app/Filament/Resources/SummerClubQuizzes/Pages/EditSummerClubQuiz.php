<?php

namespace App\Filament\Resources\SummerClubQuizzes\Pages;

use App\Filament\Resources\SummerClubQuizzes\SummerClubQuizResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSummerClubQuiz extends EditRecord
{
    protected static string $resource = SummerClubQuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
