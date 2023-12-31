<?php

namespace App\Models;

use App\Traits\Slugger;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Writer extends Model
{
    use HasFactory,
        Slugger;

    protected $fillable = [
        'image',
        'name',
        'slug',
        'bio',
        'birth_date',
        'birth_place',
        'death_date',
        'death_place',
        'is_finished',
    ];

    protected $casts = [
        'is_finished' => 'boolean',
    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function readBooks(): HasMany
    {
        return $this->hasMany(Book::class)
            ->where('is_finished', true);
    }

    public function readingProgress(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->books->count() > 0
                ? $this->readBooks->count() / $this->books->count() * 100
                : null,
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function (Writer $writer) {
            if ($writer->is_finished) {
                // if the writer is finished, make finished all books
                $writer->books()->update([
                    'is_finished' => true,
                ]);
            }
        });

        static::updating(function (Writer $writer) {
            if ($writer->is_finished) {
                // if the writer is finished, make finished all books
                $writer->books()->update([
                    'is_finished' => true,
                ]);
            }
        });
    }
}
