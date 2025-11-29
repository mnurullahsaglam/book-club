<?php

namespace App\Models;

use Filament\Actions\Action;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Notifications\BookFinishedNotification;
use App\Traits\Slugger;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Book extends Model
{
    use HasFactory,
        Slugger;

    protected $fillable = [
        'writer_id',
        'publisher_id',
        'image',
        'name',
        'original_name',
        'slug',
        'page_count',
        'is_finished',
        'publication_date',
        'publication_location',
    ];

    protected $casts = [
        'is_finished' => 'boolean',
    ];

    public function writer(): BelongsTo
    {
        return $this->belongsTo(Writer::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function meetings(): MorphMany
    {
        return $this->morphMany(Meeting::class, 'meetable');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => asset('uploads/'.$this->image),
        );
    }

    public function scopeFinished(Builder $query): Builder
    {
        return $query->where('is_finished', true);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function (Book $book) {
            if ($book->is_finished) {
                $users = User::active()->get();

                foreach ($users as $user) {
                    $book->reviews()->create([
                        'user_id' => $user->id,
                        'book_id' => $book->id,
                        'comment' => '',
                    ]);

                    Notification::make()
                        ->title('Yeni bir kitap bitti ve değerlendirmenizi bekliyor')
                        ->icon('heroicon-o-star')
                        ->iconColor('info')
                        ->sendToDatabase($user);
                }
            }
        });

        static::updating(function (Book $book) {
            if (! $book->is_finished || $book->reviews()->exists()) {
                return;
            }

            $users = User::active()->get();

            foreach ($users as $user) {
                $book->reviews()->create([
                    'user_id' => $user->id,
                ]);

                Notification::make()
                    ->title('Yeni bir kitap bitti ve değerlendirmenizi bekliyor')
                    ->icon('heroicon-o-star')
                    ->iconColor('info')
                    ->actions([
                        Action::make('make_review')
                            ->label('Değerlendir')
                            ->button()
                            ->url(ReviewResource::getUrl())
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($user);

                $user->notify(new BookFinishedNotification($book->writer->name, $book->name));
            }
        });
    }
}
