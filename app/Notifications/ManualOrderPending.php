<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ManualOrderPending extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $total    = number_format($this->order->total_cents / 100, 2);
        $currency = $this->order->currency ?? 'TND';

        return [
            'type'     => 'manual_order',
            'icon'     => '🕐',
            'title'    => 'Paiement manuel en attente',
            'message'  => $this->order->user->name . ' a demandé un accès manuel — ' . $total . ' ' . $currency . ' (commande #' . $this->order->id . ')',
            'order_id' => $this->order->id,
            'user_id'  => $this->order->user_id,
            'url'      => '/admin/orders/' . $this->order->id . '/edit',
        ];
    }
}
