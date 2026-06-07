<?php

namespace App\Filament\Resources\SummerClubEnrollments\Pages;

use App\Filament\Resources\SummerClubEnrollments\SummerClubEnrollmentResource;
use App\Models\SummerClubSubscriptionRequest;
use Filament\Resources\Pages\CreateRecord;

class CreateSummerClubEnrollment extends CreateRecord
{
    protected static string $resource = SummerClubEnrollmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return SummerClubSubscriptionRequest::normalizeEnrollmentData($data);
    }
}
