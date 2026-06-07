<?php

namespace App\Filament\Resources\SummerClubEnrollments\Pages;

use App\Filament\Resources\SummerClubEnrollments\SummerClubEnrollmentResource;
use App\Models\SummerClubSubscriptionRequest;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSummerClubEnrollment extends EditRecord
{
    protected static string $resource = SummerClubEnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return SummerClubSubscriptionRequest::normalizeEnrollmentData($data);
    }
}
