<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

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

        $admins->pluck('email')
            ->merge(config('zaytouna.admin_emails', []))
            ->map(fn ($email) => mb_strtolower(trim((string) $email)))
            ->unique()
            ->each(fn ($email) => self::mailTo($email, $notification));
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
