<?php

namespace App\Support;

use App\Models\User;
use App\Services\PlatformBridge;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as NotificationFacade;

use function Illuminate\Support\defer;

/**
 * Envoie une notification aux administrateurs :
 *  - dans la cloche Filament (canal database) de chaque compte admin ;
 *  - par e-mail, une seule fois par adresse, aux comptes admin + ADMIN_NOTIFICATION_EMAILS.
 *
 * Les notifications concernées choisissent leur canal via channelsFor() :
 * database pour un compte, mail pour une simple adresse.
 *
 * Un échec d'envoi (SMTP mal configuré…) est journalisé mais ne bloque jamais
 * l'action du visiteur ou de l'admin.
 */
class AdminNotifier
{
    public static function send(Notification $notification): void
    {
        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->get();

        foreach ($admins as $admin) {
            try {
                $admin->notifyNow($notification, ['database']);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        self::recipients($admins)->each(fn ($email) => self::mailTo($email, $notification));

        self::relayToPlatform($notification, $admins->first() ?? new User);
    }

    /** Adresses e-mail des admins + ADMIN_NOTIFICATION_EMAILS, sans doublon. */
    public static function recipients(?Collection $admins = null): Collection
    {
        $admins ??= User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->get();

        return $admins->pluck('email')
            ->merge(config('zaytouna.admin_emails', []))
            ->map(fn ($email) => mb_strtolower(trim((string) $email)))
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Relaie l'alerte dans la cloche des admins de la plateforme d'enseignement, sans donnée personnelle
     * (PlatformBridge::pushNotification), après la réponse HTTP pour ne jamais ralentir le visiteur.
     */
    private static function relayToPlatform(Notification $notification, User $notifiable): void
    {
        $bridge = app(PlatformBridge::class);
        if (! $bridge->configured() || ! method_exists($notification, 'toDatabase')) {
            return;
        }

        try {
            $data = (array) $notification->toDatabase($notifiable);
        } catch (\Throwable $e) {
            report($e);

            return;
        }

        defer(fn () => $bridge->pushNotification($data));
    }

    /** Envoie la notification par e-mail à une adresse ; false si l'envoi a échoué. */
    public static function mailTo(?string $email, Notification $notification): bool
    {
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            NotificationFacade::route('mail', $email)->notifyNow($notification);

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    public static function channelsFor(object $notifiable): array
    {
        return $notifiable instanceof AnonymousNotifiable ? ['mail'] : ['database'];
    }
}
