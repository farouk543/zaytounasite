<?php

namespace App\Notifications;

use App\Models\SummerClubSubscriptionRequest;
use App\Services\CurrencyService;
use App\Support\AdminNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SummerClubSubscriptionPending extends Notification
{
    use Queueable;

    public function __construct(
        public readonly SummerClubSubscriptionRequest $request
    ) {}

    public function via(object $notifiable): array
    {
        return AdminNotifier::channelsFor($notifiable);
    }

    public function toDatabase(object $notifiable): array
    {
        $r = $this->request;

        return [
            'type'    => 'summer_club_request',
            'icon'    => '☀️',
            'title'   => 'Club d\'été — ' . $r->pack_name,
            'message' => $r->parent_name . ' pour ' . $r->student_name . ' — ' . implode(', ', $r->selected_subjects ?? [])
                . ' — ' . $this->price() . ' (tél. ' . $r->phone . ')',
            'url'     => $this->adminPath(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $r = $this->request;

        return (new MailMessage)
            ->subject('Club d\'été : nouvelle demande — ' . $r->pack_name . ' — ' . $r->student_name)
            ->greeting('Nouvelle demande d\'abonnement au Club d\'été')
            ->line('**Pack :** ' . $r->pack_name . ' (' . $r->duration_months . ' mois)')
            ->line('**Matières :** ' . implode(', ', $r->selected_subjects ?? []))
            ->line('**Prix :** ' . $this->price())
            ->line('**Parent :** ' . $r->parent_name)
            ->line('**Élève :** ' . $r->student_name)
            ->line('**Téléphone :** ' . $r->phone)
            ->line('**E-mail :** ' . ($r->email ?: '—'))
            ->action('Traiter la demande', url($this->adminPath()));
    }

    private function price(): string
    {
        return CurrencyService::format((float) $this->request->price, $this->request->currency ?? 'TND');
    }

    private function adminPath(): string
    {
        return '/admin/summer-club-subscription-requests/' . $this->request->id . '/edit';
    }
}
