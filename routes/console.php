<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Misaf\VendraStore\Jobs\RecordStorefrontRuntimeHealthJob;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('horizon:snapshot')->everyFiveMinutes();
Schedule::command('vendra-store:reconcile')->everyFiveMinutes()->withoutOverlapping();
Schedule::job(new RecordStorefrontRuntimeHealthJob)->everyMinute();
