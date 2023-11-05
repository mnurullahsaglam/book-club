<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MeetingUser extends Pivot
{
    protected $fillable = [
        'meeting_id',
        'user_id',
        'is_participated',
        'reason_for_not_participating',
    ];

    protected $casts = [
        'is_participated' => 'boolean',
    ];

    public $timestamps = false;

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
