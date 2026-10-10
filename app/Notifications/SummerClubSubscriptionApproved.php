<?php

namespace App\Notifications;

use App\Models\SummerClubEnrollment;
use App\Models\SummerClubSubscriptionRequest;
use App\Services\CurrencyService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * E-mail envoyé au parent quand l'admin accepte une demande Club d'été.
 */
class SummerClubSubscriptionApproved extends Notification
{
    use Queueable;

    public function __construct(
        public readonly SummerClubSubscriptionRequest $request,
        public readonly SummerClubEnrollment $enrollment,
        public readonly array $access
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $r = $this->request;

        return (new MailMessage)
            ->subject('Club d\'été Zaytouna : abonnement de ' . $r->student_name . ' confirmé')
            ->markdown('mail.summer-club-subscription-approved', [
                'request'      => $r,
                'enrollment'   => $this->enrollment,
                'price'        => CurrencyService::format((float) $r->price, $r->currency ?? 'TND'),
                'level'        => SummerClubSubscriptionRequest::levelLabel($this->enrollment->level),
                'access'       => $this->access,
                'contactPhone' => config('zaytouna.contact_phone'),
                'contactEmail' => config('zaytouna.contact_email') ?: config('mail.from.address'),
            ]);
    }
}
