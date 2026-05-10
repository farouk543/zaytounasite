<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RegimeOrderPending extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Order $order,
        public readonly string $slug,
        public readonly array $selection
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $pack    = $this->selection['pack'] ?? '—';
        $isCustom = $pack === 'custom';
        $plan    = $this->selection['plan'] ?? null;
        $total   = $this->order->total_cents > 0
            ? number_format($this->order->total_cents / 100, 2) . ' ' . ($this->order->currency ?? 'TND')
            : 'À définir';

        $packLabel = match ($pack) {
            'individual' => 'Pack Individuel',
            'duo'        => 'Pack Duo',
            'group'      => 'Pack Groupe',
            'recorded'   => 'Contenu enregistré',
            'custom'     => 'Pack Personnalisé',
            default      => $pack,
        };

        $regimeLabel = match ($this->slug) {
            'tunisia' => 'Tunisie',
            'qatar'   => 'Qatar',
            'saudi'   => 'Arabie Saoudite',
            'quran'   => 'Quran',
            default   => $this->slug,
        };

        $details = $isCustom
            ? count($this->selection['items'] ?? []) . ' matière(s) — ' . $plan . ' — ' . $total
            : $packLabel . ' — ' . $regimeLabel . ' — ' . $total;

        return [
            'type'     => 'regime_order',
            'icon'     => '📚',
            'title'    => 'Demande régime ' . $regimeLabel,
            'message'  => $this->order->user->name . ' — ' . $details . ' (commande #' . $this->order->id . ')',
            'order_id' => $this->order->id,
            'user_id'  => $this->order->user_id,
            'url'      => '/admin/orders/' . $this->order->id . '/edit',
        ];
    }
}
