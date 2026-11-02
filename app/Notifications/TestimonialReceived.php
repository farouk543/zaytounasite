<?php

namespace App\Notifications;

use App\Models\Testimonial;
use App\Support\AdminNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TestimonialReceived extends Notification
{
    use Queueable;

    public function __construct(public readonly Testimonial $testimonial) {}

    public function via(object $notifiable): array
    {
        return AdminNotifier::channelsFor($notifiable);
    }

    public function toDatabase(object $notifiable): array
    {
        $t = $this->testimonial;

        return [
            'type' => 'testimonial',
            'icon' => '⭐',
            'title' => 'Nouvel avis ('.$t->rating.'/5) — '.$t->author_name,
            'message' => Str::limit($t->quote, 140).' — à valider dans Témoignages',
            'url' => '/admin/temoignages',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $t = $this->testimonial;

        return (new MailMessage)
            ->subject('Nouvel avis parent ('.$t->rating.'/5) — '.$t->author_name)
            ->greeting('Un parent a laissé un avis')
            ->line('**Note :** '.str_repeat('★', $t->rating).str_repeat('☆', 5 - $t->rating))
            ->line('**Auteur :** '.$t->author_name.($t->author_role ? ' — '.$t->author_role : ''))
            ->line('**Programme :** '.$t->programLabel())
            ->line('**Avis :** '.$t->quote)
            ->line('Il n’est pas encore publié : validez-le dans le back-office.')
            ->action('Voir les témoignages', url('/admin/temoignages'));
    }
}
