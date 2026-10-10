<?php

namespace App\Notifications;

use App\Models\User;
use App\Support\AdminNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserRegistered extends Notification
{
    use Queueable;

    public function __construct(
        public readonly User $newUser
    ) {}

    public function via(object $notifiable): array
    {
        return AdminNotifier::channelsFor($notifiable);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'new_user',
            'icon'    => '👤',
            'title'   => 'Nouvel inscrit',
            'message' => $this->newUser->name . ' (' . $this->newUser->email . ') vient de s\'inscrire.',
            'user_id' => $this->newUser->id,
            'url'     => '/admin/users/' . $this->newUser->id . '/edit',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvel inscrit : ' . $this->newUser->name)
            ->greeting('Nouvelle inscription sur Zaytouna Academy')
            ->line('**Nom :** ' . $this->newUser->name)
            ->line('**E-mail :** ' . $this->newUser->email)
            ->line('**Date :** ' . $this->newUser->created_at?->format('d/m/Y H:i'))
            ->action('Voir le compte', url('/admin/users/' . $this->newUser->id . '/edit'));
    }
}
