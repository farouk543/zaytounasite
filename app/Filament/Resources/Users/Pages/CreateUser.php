<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $state = $this->form->getState();

        // roles est un champ multiple => tableau
        $roles = $state['roles'] ?? [];

        // si vide => student
        if (empty($roles)) {
            $roles = ['student'];
        }

        // normalise au cas où
        $roles = array_values(array_filter((array) $roles));

        $this->record->syncRoles($roles);
    }
}