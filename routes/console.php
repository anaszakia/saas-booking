<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduler pembersih slot booking hold yang kedaluwarsa setiap menit
Schedule::command('bookings:release-holds')
    ->everyMinute()
    ->runInBackground()
    ->withoutOverlapping();
