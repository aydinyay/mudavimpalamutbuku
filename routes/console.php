<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Google yorumları her 2 saatte bir çek
Schedule::command('reviews:sync-google')->everyTwoHours();

// Instagram postları her saatte bir çek
Schedule::command('instagram:sync')->hourly();
