<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// ->weeklyOn() 0 = Domingo, 1 = Lunes, ..., 6 = Sábado
//Schedule::command('app:alertas-pre-inscriptos')->weeklyOn(0, '11:47');
Schedule::command('app:alertas-pre-inscriptos')->everyMinute();
