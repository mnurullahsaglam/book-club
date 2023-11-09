<?php

namespace App\Models;

use App\Traits\Slugger;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory,
        Slugger;

    protected $fillable = [
        'writer_id',
        'publisher_id',
        'image',
        'name',
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

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => asset('uploads/' . $this->image),
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function (Book $book) {
            if ($book->is_finished) {
                // if the book is finished, create review for each user
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
            if ($book->is_finished) {
                // if the book is finished and no reviews, create review for each user
                if ($book->reviews()->count() === 0) {
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
            }
        });
    }
}
