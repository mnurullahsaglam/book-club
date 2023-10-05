<?php

namespace App\Models;

use App\Traits\Slugger;
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
}
