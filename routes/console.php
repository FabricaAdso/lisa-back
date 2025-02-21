<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

app(Schedule::class)->command('updateExpiredJustification:command')->daily(); //terminada de forma satisfactoria
app(Schedule::class)->command('ApprenticeUploadJustification:command')->everySecond(); //terminada
app(Schedule::class)->command('NotifyInstructorJustifications:command')->everySecond(); //terminada