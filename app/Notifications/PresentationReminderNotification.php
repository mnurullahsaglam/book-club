<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class PresentationReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Collection $presentations)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject('Sunum Hatırlatıcısı')
            ->greeting('Merhaba!')
            ->line('Önümüzdeki toplantıda sunum(ları) yapmanız bekleniyor. Aşağıdaki detayları kontrol edin:');

        foreach ($this->presentations as $presentation) {
            $mailMessage->line("- {$presentation->citation}");

            if ($presentation->file_ur) {
                $mailMessage->attach($presentation->file_url, [
                    'as' => $presentation->title . '.pdf',
                    'mime' => 'application/pdf',
                ]);
            }
        }

        $mailMessage->salutation('Keyifli okumalar,');

        return $mailMessage;
    }
}
