<?php

namespace App\Notifications;

use App\Models\Presentation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PresentationReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Presentation $presentation)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Sunum Hatırlatıcısı')
            ->greeting('Merhaba!')
            ->line('Önümüzdeki toplantıda sunum yapmanız bekleniyor. Aşağıdaki detayları kontrol edin:')
            ->line("**{$this->presentation->citation}**")
            ->when($this->presentation->file, function (MailMessage $mailMessage) {
                $mailMessage->line('Dosyayı ekte bulabilir veya aşağıdaki butona tıklayarak tarayıcı üzerinde görüntüleyebilirsiniz.');
            })
            ->when($this->presentation->file, function (MailMessage $mailMessage) {
                $mailMessage->action('Dosyayı Görüntüle', $this->presentation->file_url);
            })
            ->salutation('Keyifli okumalar,')
            ->when($this->presentation->file, function (MailMessage $mailMessage) {
                $mailMessage->attach($this->presentation->file_url, [
                    'as' => $this->presentation->title.'.pdf',
                    'mime' => 'application/pdf',
                ]);
            });
    }
}
