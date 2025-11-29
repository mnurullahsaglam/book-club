<?php

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class PresentationReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Meeting $meeting,
        private readonly Collection $presentations,
        private readonly int $daysUntil
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $daysText = $this->daysUntil === 1 ? '1 gün' : "{$this->daysUntil} gün";
        $presentationCount = $this->presentations->count();
        $presentationText = $presentationCount === 1 ? 'sunum' : 'sunumlar';

        $mailMessage = (new MailMessage)
            ->subject("Sunum Hatırlatıcısı - {$daysText} kaldı")
            ->greeting('Merhaba '.$notifiable->name.'!')
            ->line('Yaklaşan bir toplantı için sunum hatırlatması:')
            ->line("**{$this->meeting->title}**")
            ->line("📅 Tarih: {$this->meeting->date->format('d F Y')}")
            ->line("📍 Mekân: {$this->meeting->location}")
            ->line("Toplantıya **{$daysText}** kaldı ve sizin **{$presentationCount} {$presentationText}** hazırlamanız gerekiyor:")
            ->line('');

        foreach ($this->presentations as $presentation) {
            $mailMessage->line("• **{$presentation->title}**");

            if ($presentation->citation) {
                $mailMessage->line("  Künye: {$presentation->citation}");
            }

            if ($presentation->file) {
                $mailMessage->line("  Dosya: {$presentation->file_url}");
            }

            $mailMessage->line('');
        }

        if ($this->meeting->meetable) {
            $meetable = $this->meeting->meetable;
            $mailMessage->line("📚 Toplantı Konusu: {$meetable->name}");
        }

        $mailMessage->salutation('Başarılar dileriz!');

        return $mailMessage;
    }
}
