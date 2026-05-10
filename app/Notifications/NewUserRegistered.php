<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewUserRegistered extends Notification
{
    use Queueable;

    public function __construct(
        public readonly User $newUser
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
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
}
