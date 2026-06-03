<?php

namespace App\Modules\Reservation\Models;

use App\Modules\TablePlan\Models\Lounger;
use App\Modules\TablePlan\Models\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Reservation extends Model
{
    protected $fillable = [
        'reservation_uuid', 'type',
        'guest_name', 'guest_phone', 'guest_email', 'guest_locale',
        'guest_count', 'special_requests',
        'table_id', 'lounger_id',
        'reservation_date', 'arrival_time', 'all_day',
        'status', 'confirmed_at', 'cancelled_at', 'cancellation_reason',
        'source', 'internal_notes',
    ];

    protected $casts = [
        'all_day'          => 'boolean',
        'reservation_date' => 'date',
        'confirmed_at'     => 'datetime',
        'cancelled_at'     => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $reservation) {
            if (empty($reservation->reservation_uuid)) {
                $reservation->reservation_uuid = (string) Str::uuid();
            }
        });
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function lounger(): BelongsTo
    {
        return $this->belongsTo(Lounger::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(ReservationReminder::class);
    }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'confirmed' => 'status-confirmed',
            'cancelled' => 'status-cancelled',
            'no_show'   => 'status-no_show',
            'completed' => 'status-completed',
            default     => 'status-pending',
        };
    }
}
