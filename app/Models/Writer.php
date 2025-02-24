<?php

namespace App\Models;

use App\Traits\Slugger;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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

    public function meetings(): MorphMany
    {
        return $this->morphMany(Meeting::class, 'meetable');
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function readBooks(): HasMany
    {
        return $this->hasMany(Book::class)
            ->where('is_finished', true);
    }

    public function allRelatedMeetings(): Collection
    {
        $directMeetings = $this->meetings;

        $bookIds = $this->books->pluck('id');

        $bookMeetings = Meeting::where('meetable_type', Book::class)
            ->whereIn('meetable_id', $bookIds)
            ->get();

        return $directMeetings
            ->merge($bookMeetings)
            ->sortBy('date')
            ->values();
    }

    public function readingProgress(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->books->count() > 0
                ? number_format($this->readBooks->count() / $this->books->count() * 100, 2)
                : null,
        );
    }

    protected function personalInformationText(): Attribute
    {
        return Attribute::make(
            get: function () {
                $summaryText = 'Yazarın adı: '.$this->name."\n";

                if ($this->birth_place && $this->birth_date) {
                    $summaryText .= 'Yazarın doğum yeri ve tarihi: '.$this->birth_place.', '.$this->birth_date."\n";
                }

                if ($this->death_place && $this->death_date) {
                    $summaryText .= 'Yazarın ölüm yeri ve tarihi: '.$this->death_place.', '.$this->death_date."\n";
                }

                if ($this->bio) {
                    $summaryText .= 'Yazarın biyografisi: '.$this->bio."\n";
                }
            },
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
