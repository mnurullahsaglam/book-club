<?php

namespace App\Notifications;

use App\Models\Presentation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PresentationAssignedNotification extends Notification
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
            ->subject('Yeni Sunum Atandı')
            ->greeting('Merhaba!')
            ->line('Önümüzdeki toplantı için bir sunum atandı.')
            ->when($this->presentation->file, function (MailMessage $mailMessage) {
                $mailMessage->line('Dosyayı ekte bulabilir veya aşağıdaki butona tıklayarak tarayıcı üzerinde görüntüleyebilirsiniz.');
            }, function (MailMessage $mailMessage) {
                $mailMessage->line('Detayları panel üzerinden sunumu görüntüleyebilirsiniz.');
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
