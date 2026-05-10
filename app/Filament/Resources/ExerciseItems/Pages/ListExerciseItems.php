<?php

namespace App\Filament\Resources\ExerciseItems\Pages;

use App\Filament\Resources\ExerciseItems\ExerciseItemResource;
use Filament\Resources\Pages\ListRecords;

class ListExerciseItems extends ListRecords
{
    protected static string $resource = ExerciseItemResource::class;
}
