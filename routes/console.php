<?php

use App\Jobs\SendDailyContactRequests;
use App\Jobs\SendDailyStudySubmittedNotifications;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// check cf web addresses daily at 2:00 AM
Schedule::command('cf:web-check')->dailyAt('02:00')->withoutOverlapping();

// expire core facilities
Schedule::command('cf:expire')->dailyAt('02:00')->withoutOverlapping();

// send expiration email daily at 2:00 AM
Schedule::command('cf:refresh-email')->dailyAt('02:00')->withoutOverlapping();

// send embargo reminder emails daily at 2:00 AM
Schedule::command('embargo:send-reminders')->dailyAt('02:00')->withoutOverlapping();

// send daily contact requests email at 9:00 AM
Schedule::job(new SendDailyContactRequests)->dailyAt('09:00')->withoutOverlapping();

// send daily study submitted digest email at 5:00 PM
Schedule::job(new SendDailyStudySubmittedNotifications)->dailyAt('17:00')->withoutOverlapping();

// Generate office hours sessions 8 weeks out — runs every Thursday
Schedule::command('office-hours:generate')->weekly()->thursdays()->at('00:01')->withoutOverlapping();

// Send admin reminder every Wednesday morning at 8:00 AM
Schedule::command('office-hours:remind')->weeklyOn(3, '08:00')->withoutOverlapping();

// Check for empty sessions every Wednesday at noon
Schedule::command('office-hours:check-empty')->weeklyOn(3, '12:00')->withoutOverlapping();

// Soft-delete office hours sessions whose date has passed — runs daily at 2:00 AM
Schedule::command('office-hours:prune')->dailyAt('02:00')->withoutOverlapping();
