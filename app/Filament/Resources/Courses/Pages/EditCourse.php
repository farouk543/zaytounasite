<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Resources\Courses\CourseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $course = $this->getRecord();

        $data['level_id_virtual'] = $course->subject?->level_id;
        $data['track_id_virtual'] = $course->subject?->level?->track_id;
        $data['branch_id_virtual'] = $course->subject?->branch_id;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}