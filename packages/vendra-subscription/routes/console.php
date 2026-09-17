<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('vendra-subscription:enforce')
    ->daily();

Schedule::command('vendra-subscription:recover-payments')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('vendra-subscription:report-payment-backlog')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->onOneServer();
