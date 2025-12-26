<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyHadithNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly array $hadith
    ) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Günün Hadisi - #'.$this->hadith['hadith_id'])
            ->markdown('mail.hadith.daily', [
                'hadith' => $this->hadith,
            ]);
    }
}
