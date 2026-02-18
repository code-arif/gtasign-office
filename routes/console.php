<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::command('orders:auto-complete')->dailyAt('02:00');
Schedule::command('escrow:release')->dailyAt('03:00');
Schedule::command('offers:expire')->hourly();
