<?php

namespace App\Filament\Resources\Exercises\Pages;

use App\Filament\Resources\Exercises\ExerciseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExercise extends CreateRecord
{
    protected static string $resource = ExerciseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Un seul pivot actif à la fois
        if (! empty($data['course_id'])) {
            $data['course_pack_item_id'] = null;
        }

        if (! empty($data['course_pack_item_id'])) {
            $data['course_id'] = null;
        }

        return $data;
    }
}
