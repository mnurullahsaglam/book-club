<?php

namespace App\Notifications;

use App\Filament\Resources\ReviewResource;
use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Notifications\Messages\MailMessage;
    use Illuminate\Notifications\Notification;
class BookFinishedNotification extends Notification implements ShouldQueue {
    use Queueable;

    public function __construct(private readonly string $writerName, private readonly string $bookName)
    {
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Bir Kitap Bitirildi')
            ->greeting('Merhaba!')
            ->line( sprintf('%s/%s kitabını bitirdik. Değerlendirmek için aşağıdaki butona tıklayabilirsiniz.', $this->writerName, $this->bookName) )
            ->action('Değerlendir', ReviewResource::getUrl())
            ->salutation('Keyifli okumalar,');
    }
}
