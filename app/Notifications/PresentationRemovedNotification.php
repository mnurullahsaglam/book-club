<?php

namespace App\Notifications;

use App\Models\Presentation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PresentationRemovedNotification extends Notification
{
    public function __construct(private readonly Presentation $presentation)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Sunum Başkasına Atandı')
            ->greeting('Merhaba!')
            ->line('Önümüzdeki toplantı için size verilen sunum silindi.')
            ->line("**{$this->presentation->citation}**")
            ->line('Lütfen size başka sunumlar atanıp atanmadığını kontrol edin.')
            ->salutation('Keyifli okumalar,');
    }
}
