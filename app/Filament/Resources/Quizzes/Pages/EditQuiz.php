<?php

namespace App\Filament\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\QuizResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuiz extends EditRecord
{
    protected static string $resource = QuizResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['course_id'])) {
            $data['course_pack_item_id'] = null;
        }

        if (! empty($data['course_pack_item_id'])) {
            $data['course_id'] = null;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}