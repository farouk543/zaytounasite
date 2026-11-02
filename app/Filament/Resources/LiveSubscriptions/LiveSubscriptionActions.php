<?php

namespace App\Filament\Resources\LiveSubscriptions;

use App\Models\Order;
use App\Notifications\OrderApproved;
use App\Notifications\RegimeSubscriptionApproved;
use App\Support\AdminNotifier;
use Filament\Actions\Action;
use App\Models\PageView;
use App\Services\PlatformBridge;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Actions partagées entre la liste et la fiche d'un abonnement live.
 */
class LiveSubscriptionActions
{
    /** Mot de passe provisoire renvoyé par la plateforme : gardé chiffré 2 h, le temps d'envoyer les accès. */
    private const TEMP_PASSWORD_TTL_MINUTES = 120;

    public static function createPlatformAccount(): Action
    {
        return Action::make('createPlatformAccount')
            ->label('Créer le compte sur la plateforme')
            ->icon('heroicon-o-user-plus')
            ->color('info')
            ->visible(fn (Order $record) => in_array($record->status, ['pending_manual', 'paid'], true)
                && blank($record->platform_username)
                && app(PlatformBridge::class)->configured())
            ->modalHeading(fn (Order $record) => 'Compte élève pour l\'abonnement #' . $record->id)
            ->modalDescription('Le compte est créé directement sur la plateforme de cours, avec un mot de passe provisoire (l\'élève le change à la 1re connexion). Identifiant et mot de passe sont ensuite pré-remplis dans « Accepter & envoyer les accès ».')
            ->modalSubmitActionLabel('Créer le compte')
            ->fillForm(fn (Order $record) => [
                'name' => $record->student_name ?: $record->user?->name,
                'email' => $record->parentEmail(),
                'timezone' => self::guessTimezone($record),
            ])
            ->schema([
                TextInput::make('name')->label('Nom de l\'élève')->required()->maxLength(255),
                TextInput::make('email')->label('E-mail de connexion de l\'élève')->email()->required()
                    ->helperText('Une adresse par élève : pour un frère ou une sœur, utilisez une autre adresse.'),
                Select::make('timezone')->label('Pays / fuseau horaire')->required()->options([
                    'Africa/Tunis' => 'Tunisie',
                    'Europe/Paris' => 'France',
                    'Asia/Qatar' => 'Qatar',
                    'Asia/Riyadh' => 'Arabie saoudite',
                    'America/Toronto' => 'Canada (Est)',
                    'America/Vancouver' => 'Canada (Ouest)',
                    'Europe/Brussels' => 'Belgique',
                    'Europe/Zurich' => 'Suisse',
                    'Asia/Dubai' => 'Émirats arabes unis',
                    'Africa/Casablanca' => 'Maroc',
                    'Africa/Algiers' => 'Algérie',
                ]),
            ])
            ->action(function (Order $record, array $data) {
                $result = app(PlatformBridge::class)->createStudent($record, $data['name'], $data['email'], $data['timezone'] ?? null);

                if (! $result['ok']) {
                    FilamentNotification::make()->title('Compte non créé')->body($result['error'] ?? 'Erreur inconnue.')->danger()->persistent()->send();

                    return;
                }

                $record->update(['platform_username' => $result['login']]);

                if (! empty($result['password'])) {
                    Cache::put(self::passwordKey($record), Crypt::encryptString($result['password']), now()->addMinutes(self::TEMP_PASSWORD_TTL_MINUTES));
                }

                $created = (bool) ($result['created'] ?? false);

                FilamentNotification::make()
                    ->title($created ? 'Compte créé sur la plateforme' : 'Compte existant relié à cet abonnement')
                    ->body($created
                        ? 'Identifiant : ' . $result['login'] . '. Le mot de passe provisoire est pré-rempli dans « Accepter & envoyer les accès » (pendant 2 h).'
                        : 'L\'élève ' . $result['login'] . ' avait déjà un compte : il garde son mot de passe actuel.')
                    ->success()
                    ->persistent()
                    ->send();
            });
    }

    /** Fuseau probable de l'élève : régime choisi, sinon pays des pages vues par le parent. */
    private static function guessTimezone(Order $record): string
    {
        if ($tz = PlatformBridge::REGIME_TIMEZONES[$record->regime] ?? null) {
            return $tz;
        }

        $country = PageView::where('user_id', $record->user_id)->whereNotNull('country')->latest('id')->value('country');

        return PlatformBridge::COUNTRY_TIMEZONES[$country] ?? 'Africa/Tunis';
    }

    private static function passwordKey(Order $record): string
    {
        return 'platform-temp-password:' . $record->id;
    }

    private static function pendingPassword(Order $record): ?string
    {
        try {
            $encrypted = Cache::get(self::passwordKey($record));

            return $encrypted ? Crypt::decryptString($encrypted) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function approve(): Action
    {
        return Action::make('approve')
            ->label('Accepter & envoyer les accès')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (Order $record) => $record->status === 'pending_manual')
            ->modalHeading(fn (Order $record) => 'Accepter l\'abonnement #' . $record->id)
            ->modalDescription('Créez d\'abord le compte de l\'élève sur la plateforme de cours (bouton « Créer le compte sur la plateforme »), puis renseignez ses accès : ils seront envoyés par e-mail au parent avec le récapitulatif de l\'abonnement.')
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
                        ->helperText('Envoyé dans l\'e-mail uniquement, jamais enregistré ici (s\'il vient de « Créer le compte sur la plateforme », il reste chiffré 2 h au plus).'),
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
            'password'     => self::pendingPassword($record),
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
            Cache::forget(self::passwordKey($record));

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
