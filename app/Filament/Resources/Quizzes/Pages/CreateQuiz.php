<?php

namespace App\Filament\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\QuizResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuiz extends CreateRecord
{
    protected static string $resource = QuizResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['course_id'])) {
            $data['course_pack_item_id'] = null;
        }

        if (! empty($data['course_pack_item_id'])) {
            $data['course_id'] = null;
        }

        return $data;
    }
}