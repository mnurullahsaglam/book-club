<?php

namespace App\Notifications;

use App\Models\Presentation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PresentationMeetingUpdatedNotification extends Notification
{
    public function __construct(private readonly Presentation $presentation, private readonly string $oldDate)
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
            ->subject('Sunum Tarihi Değişti')
            ->greeting('Merhaba!')
            ->line($this->oldDate . ' tarihli toplantı için atanan sunumun tarihi değişti.')
            ->line("**{$this->presentation->citation}**")
            ->line('Yeni tarih: ' . $this->presentation->meeting->date->format('d/m/Y'))
            ->when($this->presentation->file, function (MailMessage $mailMessage) {
                $mailMessage->line('Dosyayı ekte bulabilir veya aşağıdaki butona tıklayarak tarayıcı üzerinde görüntüleyebilirsiniz.');
            }, function (MailMessage $mailMessage) {
                $mailMessage->line('Detayları panel üzerinden görüntüleyebilirsiniz.');
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
