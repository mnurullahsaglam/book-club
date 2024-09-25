<?php

namespace App\Notifications;

use App\Models\Writer;
use App\Services\WriterSummaryService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class WriterSummaryNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Writer $writer) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Yazar Özeti: '.$this->writer->name)
            ->greeting('Merhaba!')
            ->line('Yazarın özeti aşağıdaki gibidir.')
            ->line(new HtmlString((new WriterSummaryService($this->writer))->handle()))
            ->salutation('Keyifli okumalar,');
    }
}
