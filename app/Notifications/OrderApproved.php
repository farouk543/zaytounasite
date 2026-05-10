<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderApproved extends Notification
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
        $isRegime = $this->order->payments()->where('provider', 'regime')->exists();

        $message = $isRegime
            ? 'Votre demande de cours a été acceptée ! L\'équipe Zaytouna vous contactera prochainement.'
            : 'Votre commande #' . $this->order->id . ' (' . $total . ' ' . $currency . ') a été validée. Vos cours sont maintenant accessibles.';

        return [
            'type'     => 'order_approved',
            'icon'     => '✅',
            'title'    => 'Demande acceptée',
            'message'  => $message,
            'order_id' => $this->order->id,
            'url'      => '/my-courses',
        ];
    }
}
