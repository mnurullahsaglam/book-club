<?php

namespace App\Models;

use App\Traits\Slugger;
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
}
