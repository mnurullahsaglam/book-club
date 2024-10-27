<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'order',
        'title',
        'date',
        'location',
        'guests',
        'topics',
        'decisions',
    ];

    protected $casts = [
        'guests' => 'array',
        'date' => 'date',
    ];

    public function meetable(): MorphTo
    {
        return $this->morphTo();
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function presentations(): HasMany
    {
        return $this->hasMany(Presentation::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('reason_for_not_participating');
    }

    public function participatedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('reason_for_not_participating')
            ->wherePivot('is_participated', true);
    }

    public function abstainedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('reason_for_not_participating')
            ->wherePivot('is_participated', false);
    }

    public function additionalDocuments(): HasMany
    {
        return $this->hasMany(AdditionalDocument::class);
    }

    public function meetingUsers(): HasMany
    {
        return $this->hasMany(MeetingUser::class);
    }

    public function orderedTitle(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->order.'. '.$this->title,
        );
    }

    public function scopePast(Builder $query): void
    {
        $query->where('date', '<=', now());
    }
}
