<?php

namespace App\Models;

use App\Notifications\PresentationAssignedNotification;
use App\Notifications\PresentationMeetingUpdatedNotification;
use App\Notifications\PresentationRemovedNotification;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'meeting_id',
        'title',
        'author',
        'publication_year',
        'file',
        'description',
        'citation',
        'is_recommended',
    ];

    protected $casts = [
        'is_recommended' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function (Presentation $presentation) {
            $user = $presentation->user;

            $notificationText = "{$presentation->meeting->date->format('d/m/Y')} tarihindeki toplantı için sunumunuz sisteme yüklendi.";

            $user->notify(
                Notification::make()
                    ->title('Yeni Sunum Yüklendi')
                    ->body($notificationText)
                    ->icon('heroicon-o-document-check')
                    ->iconColor('info')
                    ->actions([
                        Action::make('view')
                            ->label('Görüntüle')
                            ->button()
                            ->url($presentation->file_url)
                            ->openUrlInNewTab()
                            ->visible(fn () => $presentation->file)
                            ->markAsRead(),
                    ])
                    ->toDatabase()
            );

            $user->notify(new PresentationAssignedNotification($presentation));
        });

        self::updating(function (Presentation $presentation) {
            if ($presentation->isDirty('meeting_id')) {
                $oldMeeting = Meeting::find($presentation->getOriginal('meeting_id'));

                $notificationText = "{$presentation->meeting->date->format('d/m/Y')} tarihindeki toplantı için sunumunuz güncellendi.";

                $presentation->user->notify(
                    Notification::make()
                        ->title('Sunum Güncellendi')
                        ->body($notificationText)
                        ->icon('heroicon-o-document-check')
                        ->iconColor('info')
                        ->actions([
                            Action::make('view')
                                ->label('Görüntüle')
                                ->button()
                                ->url($presentation->file_url)
                                ->openUrlInNewTab()
                                ->visible(fn () => $presentation->file)
                                ->markAsRead(),
                        ])
                        ->toDatabase()
                );

                $presentation->user->notify(new PresentationMeetingUpdatedNotification($presentation, $oldMeeting->date->format('d/m/Y')));
            }

            if ($presentation->isDirty('user_id')) {
                $oldUser = User::find($presentation->getOriginal('user_id'));

                $notificationText = "Sunumunuz yeni bir kullanıcıya atandı.";
                $presentation->user->notify(
                    Notification::make()
                        ->title('Sunum Atandı')
                        ->body($notificationText)
                        ->icon('heroicon-o-document-check')
                        ->iconColor('info')
                        ->actions([
                            Action::make('view')
                                ->label('Görüntüle')
                                ->button()
                                ->url($presentation->file_url)
                                ->openUrlInNewTab()
                                ->visible(fn () => $presentation->file)
                                ->markAsRead(),
                        ])
                        ->toDatabase()
                );

                $oldUser->notify(
                    Notification::make()
                        ->title('Sunum Başkasına Atandı')
                        ->body($notificationText)
                        ->icon('heroicon-o-document-check')
                        ->iconColor('info')
                        ->actions([
                            Action::make('view')
                                ->label('Görüntüle')
                                ->button()
                                ->url($presentation->file_url)
                                ->openUrlInNewTab()
                                ->visible(fn () => $presentation->file)
                                ->markAsRead(),
                        ])
                        ->toDatabase()
                );

                $oldUser->notify(new PresentationRemovedNotification($presentation));

                $presentation->user->notify(new PresentationAssignedNotification($presentation));
            }
        });
    }

    public function title(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => ucwords(strtolower($value)),
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function fileUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => asset('uploads/'.$this->file),
        );
    }

    public function scopeOwner(Builder $query): Builder
    {
        return $query->where('user_id', auth()->id());
    }

    protected function citation(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->citation ?? $this->generateCitation(),
        );
    }

    private function generateCitation()
    {
        $citation = $this->title;

        if ($this->author) {
            $citation .= ', '.$this->author;
        }

        if ($this->publication_year) {
            $citation .= ', '.$this->publication_year;
        }

        return $citation;
    }
}
