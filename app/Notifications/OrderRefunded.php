<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderRefunded extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Order $order,
        public readonly string $courseList = ''
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'     => 'order_refunded',
            'icon'     => '↩️',
            'title'    => 'Accès suspendu',
            'message'  => 'Votre commande #' . $this->order->id . ' a été remboursée. L\'accès à ' . ($this->courseList ?: 'vos cours') . ' a été suspendu.',
            'order_id' => $this->order->id,
            'url'      => '/my-courses',
        ];
    }
}
