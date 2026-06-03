<?php

namespace App\Modules\Core\Services;

use Carbon\Carbon;

class RestaurantService
{
    public static function isOpen(): bool
    {
        $override = config('restaurant.is_open_override');
        if (!is_null($override)) {
            return (bool) $override;
        }

        $today = Carbon::today();
        $year  = $today->year;

        [$openMonth, $openDay]   = explode('-', config('restaurant.season.open_date', '05-01'));
        [$closeMonth, $closeDay] = explode('-', config('restaurant.season.close_date', '10-31'));

        $seasonOpen  = Carbon::createFromDate($year, (int)$openMonth,  (int)$openDay);
        $seasonClose = Carbon::createFromDate($year, (int)$closeMonth, (int)$closeDay);

        return $today->between($seasonOpen, $seasonClose);
    }

    public static function seasonOpenDate(): Carbon
    {
        [$m, $d] = explode('-', config('restaurant.season.open_date', '05-01'));
        return Carbon::createFromDate(now()->year, (int)$m, (int)$d);
    }
}
