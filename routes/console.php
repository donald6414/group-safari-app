<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:check-reservation-due-date')->daily();

// Run queue worker every minute to process queued jobs
Schedule::command('queue:work --once')
    ->everyMinute()
    ->withoutOverlapping()
    ->onFailure(function () {
        // Log failure if needed
        \Log::error('Queue worker failed to process jobs');
    });
