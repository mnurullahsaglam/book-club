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
            ->from('bilgi@kitap.mnurullahsaglam.com')
            ->subject('Yeni Sunum Atandı')
            ->greeting('Merhaba!')
            ->line('Önümüzdeki toplantı için bir sunum atandı.')
            ->line("Dosyayı ekte bulabilir veya aşağıdaki butona tıklayarak tarayıcı üzerinde görüntüleyebilirsiniz.")
            ->action('Dosyayı Görüntüle', $this->presentation->file_url)
            ->salutation('Keyifli okumalar,')
            ->attach($this->presentation->file_url, [
                'as' => $this->presentation->title,
                'mime' => 'application/pdf',
            ]);
    }
}
