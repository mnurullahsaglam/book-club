<?php

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MeetingSummaryNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Meeting $meeting) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject($this->meeting->date->format('d/m/Y').' Tarihli Toplantı Özeti')
            ->markdown('mail.meetings.summary', [
                'meeting' => $this->meeting,
            ]);
    }
}
