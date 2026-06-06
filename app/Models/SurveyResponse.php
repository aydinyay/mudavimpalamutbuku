<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyResponse extends Model
{
    protected $fillable = [
        'token', 'guest_name', 'guest_phone', 'reservation_id',
        'rating', 'feedback', 'outcome', 'sent_at', 'responded_at', 'admin_read',
    ];

    protected $casts = [
        'sent_at'      => 'datetime',
        'responded_at' => 'datetime',
        'admin_read'   => 'boolean',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Reservation\Models\Reservation::class);
    }

    public function isPositive(): bool
    {
        return $this->rating >= 4;
    }
}
