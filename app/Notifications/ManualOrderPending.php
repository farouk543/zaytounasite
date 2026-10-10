<?php

namespace App\Notifications;

use App\Models\Order;
use App\Support\AdminNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ManualOrderPending extends Notification
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
        return [
            'type'     => 'manual_order',
            'icon'     => '🕐',
            'title'    => 'Paiement manuel en attente',
            'message'  => $this->order->user->name . ' a demandé un accès manuel — ' . $this->total() . ' (commande #' . $this->order->id . ')',
            'order_id' => $this->order->id,
            'user_id'  => $this->order->user_id,
            'url'      => '/admin/orders/' . $this->order->id . '/edit',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->order->loadMissing('items.course', 'user');

        $mail = (new MailMessage)
            ->subject('Nouvelle commande à valider #' . $this->order->id . ' — ' . $this->order->user->name)
            ->greeting('Nouvelle commande en attente de validation')
            ->line('**Client :** ' . $this->order->user->name . ' (' . $this->order->user->email . ')')
            ->line('**Total :** ' . $this->total());

        foreach ($this->order->items as $item) {
            $mail->line('• ' . ($item->course?->title ?? 'Article #' . $item->id));
        }

        return $mail->action('Traiter la commande', url('/admin/orders/' . $this->order->id . '/edit'));
    }

    private function total(): string
    {
        return number_format($this->order->total_cents / 100, 2) . ' ' . ($this->order->currency ?? 'TND');
    }
}
