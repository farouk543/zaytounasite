<?php

namespace App\Filament\Resources\ExerciseItems\Pages;

use App\Filament\Resources\ExerciseItems\ExerciseItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExerciseItem extends EditRecord
{
    protected static string $resource = ExerciseItemResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            '#' => 'Item exercice',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return url()->previous();
    }
}
