<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderPaid extends Notification
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
            'type'     => 'order_paid',
            'icon'     => '💳',
            'title'    => 'Paiement reçu',
            'message'  => $this->order->user->name . ' — ' . $total . ' ' . $currency . ' (commande #' . $this->order->id . ')',
            'order_id' => $this->order->id,
            'user_id'  => $this->order->user_id,
            'url'      => '/admin/orders/' . $this->order->id . '/edit',
        ];
    }
}
