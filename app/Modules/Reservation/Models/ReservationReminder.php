<?php

namespace App\Modules\Reservation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationReminder extends Model
{
    protected $fillable = [
        'reservation_id', 'type', 'message_body',
        'sent_at', 'provider_message_id', 'status',
    ];

    protected $casts = ['sent_at' => 'datetime'];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
