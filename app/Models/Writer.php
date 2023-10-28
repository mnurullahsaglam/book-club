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
    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function booksRead(): HasMany
    {
        return $this->hasMany(Book::class)
            ->where('is_finished', true);
    }

    public function readingProgress(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->books->count() > 0
                ? $this->booksRead->count() / $this->books->count() * 100
                : null,
        );
    }
}
