<?php

namespace App\Modules\Reservation\Services;

use App\Modules\TablePlan\Models\Table;
use Illuminate\Support\Collection;

class AvailabilityService
{
    // A reservation blocks a table for 2 hours on either side of arrival time
    private const BUFFER_HOURS = 2;

    public function availableTables(string $date, string $time, int $guestCount): Collection
    {
        $start = \Carbon\Carbon::parse("{$date} {$time}")->subHours(self::BUFFER_HOURS);
        $end   = \Carbon\Carbon::parse("{$date} {$time}")->addHours(self::BUFFER_HOURS);

        $startTime = $start->format('H:i:s');
        $endTime   = $end->format('H:i:s');

        // Tables that already have a conflicting reservation
        $bookedTableIds = \App\Modules\Reservation\Models\Reservation::whereDate('reservation_date', $date)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->whereNotNull('table_id')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('arrival_time', [$startTime, $endTime]);
            })
            ->pluck('table_id');

        return Table::where('is_active', true)
            ->where('is_reservable', true)
            ->where('seats_max', '>=', $guestCount)
            ->whereNotIn('id', $bookedTableIds)
            ->with('area')
            ->orderBy('seats_max')
            ->get();
    }

    public function isTableAvailable(int $tableId, string $date, string $time): bool
    {
        return $this->availableTables($date, $time, 1)
            ->contains('id', $tableId);
    }
}
