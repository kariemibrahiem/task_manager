<?php

use App\Jobs\CreateOverdueTaskNotificationsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::job(new CreateOverdueTaskNotificationsJob, 'notifications')
    ->hourly()
    ->withoutOverlapping();
