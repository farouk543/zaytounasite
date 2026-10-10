<?php

namespace App\Notifications;

use App\Models\Order;
use App\Support\AdminNotifier;
use App\Support\RegimeSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegimeOrderPending extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return AdminNotifier::channelsFor($notifiable);
    }

    public function toDatabase(object $notifiable): array
    {
        $regime = RegimeSubscription::regimeLabel($this->order->regime);
        $pack   = RegimeSubscription::packLabel($this->order->pack);

        return [
            'type'     => 'regime_order',
            'icon'     => '📚',
            'title'    => 'Nouvel abonnement — ' . $pack . ' (' . $regime . ')',
            'message'  => ($this->order->parent_name ?: $this->order->user->name)
                . ' — ' . RegimeSubscription::shortSummary($this->order)
                . ' — ' . RegimeSubscription::totalLabel($this->order)
                . ' (demande #' . $this->order->id . ')',
            'order_id' => $this->order->id,
            'user_id'  => $this->order->user_id,
            'url'      => $this->adminPath(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $o = $this->order;

        $mail = (new MailMessage)
            ->subject('Nouvel abonnement #' . $o->id . ' — ' . RegimeSubscription::packLabel($o->pack) . ' ' . RegimeSubscription::regimeLabel($o->regime))
            ->greeting('Nouvelle demande d\'abonnement (cours live)')
            ->line('**Régime :** ' . RegimeSubscription::regimeLabel($o->regime))
            ->line('**Pack :** ' . RegimeSubscription::packLabel($o->pack)
                . ($o->plan ? ' — ' . RegimeSubscription::planLabel($o->plan) : ''))
            ->line('**Montant :** ' . RegimeSubscription::totalLabel($o))
            ->line('**Parent :** ' . ($o->parent_name ?: $o->user->name))
            ->line('**Élève :** ' . ($o->student_name ?: '—'))
            ->line('**Téléphone :** ' . ($o->phone ?: '—'))
            ->line('**E-mail :** ' . ($o->parentEmail() ?: '—'))
            ->line('**Matières demandées :**');

        foreach (RegimeSubscription::detailLines($o) as $line) {
            $mail->line('• ' . $line);
        }

        if ($o->customer_notes) {
            $mail->line('**Message du parent :** ' . $o->customer_notes);
        }

        return $mail->action('Traiter la demande', url($this->adminPath()));
    }

    private function adminPath(): string
    {
        return '/admin/abonnements-live/' . $this->order->id;
    }
}
