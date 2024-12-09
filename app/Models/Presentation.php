<?php

namespace App\Models;

use App\Notifications\PresentationAssignedNotification;
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

    protected function citation(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->citation ?? $this->generateCitation(),
        );
    }

    public function scopeOwner(Builder $query): Builder
    {
        return $query->where('user_id', auth()->id());
    }

    private function generateCitation()
    {
        $citation = $this->title;

        if ($this->author) {
            $citation .= ', <i>'.$this->author.'</i>';
        }

        if ($this->publication_year) {
            $citation .= ', '.$this->publication_year;
        }

        return $citation;
    }
}
