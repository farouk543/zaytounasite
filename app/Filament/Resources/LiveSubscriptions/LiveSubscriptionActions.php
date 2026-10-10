<?php

namespace App\Filament\Resources\LiveSubscriptions;

use App\Models\Order;
use App\Notifications\OrderApproved;
use App\Notifications\RegimeSubscriptionApproved;
use App\Support\AdminNotifier;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\DB;

/**
 * Actions partagées entre la liste et la fiche d'un abonnement live.
 */
class LiveSubscriptionActions
{
    public static function approve(): Action
    {
        return Action::make('approve')
            ->label('Accepter & envoyer les accès')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (Order $record) => $record->status === 'pending_manual')
            ->modalHeading(fn (Order $record) => 'Accepter l\'abonnement #' . $record->id)
            ->modalDescription('Créez d\'abord le compte de l\'élève sur la plateforme de cours, puis renseignez ses accès : ils seront envoyés par e-mail au parent avec le récapitulatif de l\'abonnement.')
            ->modalSubmitActionLabel('Accepter et envoyer l\'e-mail')
            ->modalWidth('3xl')
            ->fillForm(fn (Order $record) => self::defaults($record))
            ->schema(self::accessForm(withNotes: true))
            ->action(function (Order $record, array $data) {
                DB::transaction(function () use ($record, $data) {
                    $record->payments()->where('provider', 'regime')->update(['status' => 'paid']);
                    $record->update([
                        'status'            => 'paid',
                        'approved_at'       => now(),
                        'approved_by'       => auth()->id(),
                        'platform_username' => $data['login'],
                        'admin_notes'       => $data['admin_notes'] ?? $record->admin_notes,
                    ]);
                });

                try {
                    $record->user?->notify(new OrderApproved($record));
                } catch (\Throwable $e) {
                    report($e);
                }

                self::sendAccessMail($record, $data, 'Abonnement accepté');
            });
    }

    public static function resendAccess(): Action
    {
        return Action::make('resendAccess')
            ->label('Renvoyer les accès')
            ->icon('heroicon-o-envelope')
            ->color('gray')
            ->visible(fn (Order $record) => $record->status === 'paid')
            ->modalHeading('Renvoyer l\'e-mail d\'accès au parent')
            ->modalSubmitActionLabel('Envoyer')
            ->modalWidth('3xl')
            ->fillForm(fn (Order $record) => self::defaults($record))
            ->schema(self::accessForm(withNotes: false))
            ->action(function (Order $record, array $data) {
                $record->update(['platform_username' => $data['login']]);

                self::sendAccessMail($record, $data, 'E-mail renvoyé');
            });
    }

    public static function reject(): Action
    {
        return Action::make('reject')
            ->label('Refuser')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (Order $record) => $record->status === 'pending_manual')
            ->requiresConfirmation()
            ->modalHeading('Refuser cette demande ?')
            ->modalDescription('La demande sera marquée comme refusée. Aucun e-mail n\'est envoyé au parent.')
            ->modalSubmitActionLabel('Oui, refuser')
            ->schema([
                Textarea::make('admin_notes')
                    ->label('Motif (interne)')
                    ->rows(3),
            ])
            ->action(function (Order $record, array $data) {
                $record->payments()->where('provider', 'regime')->update(['status' => 'failed']);
                $record->update([
                    'status'      => 'canceled',
                    'admin_notes' => $data['admin_notes'] ?? $record->admin_notes,
                ]);

                FilamentNotification::make()
                    ->title('Demande refusée')
                    ->danger()
                    ->send();
            });
    }

    private static function accessForm(bool $withNotes): array
    {
        return array_filter([
            Section::make('Compte créé sur la plateforme de cours')
                ->columns(2)
                ->schema([
                    TextInput::make('platform_url')
                        ->label('Adresse de la plateforme')
                        ->url()
                        ->columnSpanFull(),
                    TextInput::make('login')
                        ->label('Identifiant de l\'élève')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('password')
                        ->label('Mot de passe provisoire')
                        ->maxLength(255)
                        ->helperText('Envoyé dans l\'e-mail uniquement, jamais enregistré ici.'),
                ]),

            Section::make('Démarrage des cours')
                ->columns(2)
                ->schema([
                    DatePicker::make('start_date')
                        ->label('Date de début')
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                    TextInput::make('teacher')
                        ->label('Professeur')
                        ->maxLength(255),
                    Textarea::make('schedule')
                        ->label('Planning des séances')
                        ->placeholder("Mercredi 15h–16h : Mathématiques\nSamedi 10h–11h : Arabe")
                        ->rows(3)
                        ->columnSpanFull(),
                    Textarea::make('message')
                        ->label('Message au parent')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Section::make('Envoi')
                ->columns(2)
                ->schema(array_filter([
                    TextInput::make('recipient')
                        ->label('E-mail du parent')
                        ->email()
                        ->required(),
                    $withNotes
                        ? Textarea::make('admin_notes')->label('Notes internes')->rows(2)
                        : null,
                ])),
        ]);
    }

    private static function defaults(Order $record): array
    {
        return [
            'platform_url' => config('zaytouna.platform_url'),
            'login'        => $record->platform_username,
            'recipient'    => $record->parentEmail(),
            'admin_notes'  => $record->admin_notes,
        ];
    }

    private static function sendAccessMail(Order $record, array $data, string $title): void
    {
        $access = collect($data)
            ->only(['platform_url', 'login', 'password', 'start_date', 'schedule', 'teacher', 'message'])
            ->all();

        $sent = AdminNotifier::mailTo($data['recipient'], new RegimeSubscriptionApproved($record, $access));

        if ($sent) {
            $record->update(['access_sent_at' => now()]);

            FilamentNotification::make()
                ->title($title)
                ->body('Les accès ont été envoyés à ' . $data['recipient'] . '.')
                ->success()
                ->send();

            return;
        }

        FilamentNotification::make()
            ->title($title . ', mais l\'e-mail n\'a pas pu être envoyé')
            ->body('Vérifiez la configuration e-mail (MAIL_*) puis utilisez « Renvoyer les accès ».')
            ->warning()
            ->persistent()
            ->send();
    }
}
