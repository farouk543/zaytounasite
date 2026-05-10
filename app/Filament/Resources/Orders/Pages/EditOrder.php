<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Notifications\OrderApproved;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    // ✅ Lecture seule (pas de save)
    protected function getFormActions(): array
    {
        return []; // supprime le bouton "Save"
    }

    protected function getFormSchema(): array
    {
        $fmt = fn (?int $cents) =>
            number_format((($cents ?? 0) / 100), 2) . ' ' . ($this->record->currency ?? 'TND');

        return [
            Forms\Components\Section::make('Résumé commande')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('id')
                        ->label('Commande #')
                        ->disabled(),

                    Forms\Components\TextInput::make('user.email')
                        ->label('Utilisateur (email)')
                        ->disabled(),

                    Forms\Components\TextInput::make('status')
                        ->label('Statut')
                        ->disabled(),

                    Forms\Components\TextInput::make('subtotal_cents')
                        ->label('Sous-total')
                        ->formatStateUsing(fn ($state) => $fmt((int) $state))
                        ->disabled(),

                    Forms\Components\TextInput::make('discount_cents')
                        ->label('Remise')
                        ->formatStateUsing(fn ($state) => $fmt((int) $state))
                        ->disabled(),

                    Forms\Components\TextInput::make('total_cents')
                        ->label('Total')
                        ->formatStateUsing(fn ($state) => $fmt((int) $state))
                        ->disabled(),

                    Forms\Components\TextInput::make('created_at')
                        ->label('Créée le')
                        ->formatStateUsing(fn ($state) => optional($state)->format('Y-m-d H:i'))
                        ->disabled(),

                    Forms\Components\TextInput::make('coupon_code')
                        ->label('Coupon')
                        ->disabled(),
                ]),
        ];
    }

    // ✅ Empêche toute tentative de sauvegarde même si quelqu’un force une requête
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->record->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            // ── APPROUVER (pending_manual) ──────────────────────────────
            Action::make('approve')
                ->label('Approuver')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approuver cette commande ?')
                ->modalDescription('L\'accès aux cours sera activé immédiatement pour l\'utilisateur.')
                ->modalSubmitActionLabel('Oui, approuver')
                ->visible(fn () => $this->record->status === 'pending_manual')
                ->action(function () {
                    DB::transaction(function () {
                        $this->record->load('items.course', 'user');
                        $isRegime = $this->record->payments()->where('provider', 'regime')->exists();

                        if (! $isRegime) {
                            foreach ($this->record->items as $item) {
                                if (! $item->course) continue;
                                Enrollment::updateOrCreate(
                                    ['user_id' => $this->record->user_id, 'course_id' => $item->course_id],
                                    ['enrolled_at' => now(), 'access_ends_at' => null, 'status' => 'active']
                                );
                            }
                            $this->record->payments()->where('provider', 'manual')->update(['status' => 'paid']);
                        } else {
                            $this->record->payments()->where('provider', 'regime')->update(['status' => 'paid']);
                        }

                        $this->record->update(['status' => 'paid']);
                    });

                    try {
                        $this->record->load('user');
                        $this->record->user?->notify(new OrderApproved($this->record));
                    } catch (\Throwable $e) {}

                    FilamentNotification::make()
                        ->title('Commande approuvée')
                        ->body('Les inscriptions ont été activées et l\'utilisateur est notifié.')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status']);
                }),

            // ── REJETER (pending_manual) ────────────────────────────────
            Action::make('reject')
                ->label('Rejeter')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Rejeter cette commande ?')
                ->modalDescription('La commande sera annulée. Aucun accès ne sera accordé.')
                ->modalSubmitActionLabel('Oui, rejeter')
                ->visible(fn () => $this->record->status === 'pending_manual')
                ->action(function () {
                    $this->record->payments()->where('provider', 'manual')->update(['status' => 'failed']);
                    $this->record->update(['status' => 'canceled']);

                    FilamentNotification::make()
                        ->title('Commande rejetée')
                        ->body('La commande a été annulée.')
                        ->danger()
                        ->send();

                    $this->refreshFormData(['status']);
                }),

            // ── REMBOURSER (paid) ───────────────────────────────────────
            Action::make('refund')
                ->label('Rembourser')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Rembourser cette commande ?')
                ->modalDescription('L\'accès à tous les cours de cette commande sera immédiatement suspendu et l\'utilisateur sera notifié.')
                ->modalSubmitActionLabel('Oui, rembourser')
                ->visible(fn () => $this->record->status === 'paid')
                ->action(function () {
                    $suspendedCourses = [];

                    DB::transaction(function () use (&$suspendedCourses) {
                        $this->record->update(['status' => 'refunded']);
                        $this->record->loadMissing('items.course');

                        $courseIds = $this->record->items->pluck('course_id')->filter()->unique()->values()->all();
                        $suspendedCourses = $this->record->items
                            ->pluck('course.title')
                            ->filter()
                            ->values()
                            ->all();

                        if (! empty($courseIds)) {
                            Enrollment::query()
                                ->where('user_id', $this->record->user_id)
                                ->whereIn('course_id', $courseIds)
                                ->update(['status' => 'canceled']);
                        }
                    });

                    // Notifier l'utilisateur
                    try {
                        $this->record->load('user');
                        $courseList = implode(', ', $suspendedCourses) ?: 'vos cours';
                        $this->record->user?->notify(
                            new \App\Notifications\OrderRefunded($this->record, $courseList)
                        );
                    } catch (\Throwable $e) {}

                    FilamentNotification::make()
                        ->title('Commande remboursée')
                        ->body('L\'accès aux cours a été suspendu et l\'utilisateur est notifié.')
                        ->warning()
                        ->send();

                    $this->refreshFormData(['status']);
                }),
        ];
    }
}