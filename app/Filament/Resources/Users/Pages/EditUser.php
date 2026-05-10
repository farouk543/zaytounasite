<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // pré-remplir le rôle actuel
        $data['roles'] = $this->record->roles->pluck('name')->first() ?? 'teacher';
        return $data;
    }

    protected function afterSave(): void
    {
        $role = $this->form->getState()['roles'] ?? 'teacher';
        $this->record->syncRoles([$role]);
    }
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('suspendAllAccess')
                ->label('Suspendre tous les accès')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->enrollments()->update(['status' => 'canceled']);
                    $this->notify('success', 'Tous les accès ont été suspendus.');
                }),

            \Filament\Actions\Action::make('reactivateAllAccess')
                ->label('Réactiver tous les accès')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->enrollments()->update(['status' => 'active', 'access_ends_at' => null]);
                    $this->notify('success', 'Tous les accès ont été réactivés.');
                }),
        ];
    }
}