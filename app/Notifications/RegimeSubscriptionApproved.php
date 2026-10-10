<?php

namespace App\Notifications;

use App\Models\Order;
use App\Support\RegimeSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * E-mail envoyé au parent quand l'admin accepte un abonnement live :
 * récapitulatif + identifiants du compte créé sur la plateforme de cours.
 *
 * $access : platform_url, login, password, start_date, schedule, teacher, message (tous optionnels sauf login).
 */
class RegimeSubscriptionApproved extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Order $order,
        public readonly array $access
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $o = $this->order;

        return (new MailMessage)
            ->subject('Votre abonnement Zaytouna Academy est confirmé — vos accès')
            ->markdown('mail.regime-subscription-approved', [
                'order'        => $o,
                'parentName'   => $o->parent_name ?: $o->user?->name,
                'regime'       => RegimeSubscription::regimeLabel($o->regime),
                'pack'         => RegimeSubscription::packLabel($o->pack),
                'plan'         => RegimeSubscription::planLabel($o->plan),
                'total'        => RegimeSubscription::totalLabel($o),
                'lines'        => RegimeSubscription::detailLines($o),
                'access'       => $this->access,
                'contactPhone' => config('zaytouna.contact_phone'),
                'contactEmail' => config('zaytouna.contact_email') ?: config('mail.from.address'),
            ]);
    }
}
