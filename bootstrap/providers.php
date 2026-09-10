<?php

declare(strict_types=1);
use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelServiceProvider;
use App\Providers\FilamentDefaultsServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\PulseServiceProvider;
use Misaf\VendraConsole\Providers\ConsolePanelServiceProvider;
use Misaf\VendraReseller\Providers\ResellerPanelServiceProvider;

return [
    AppServiceProvider::class,
    FilamentDefaultsServiceProvider::class,
    ResellerPanelServiceProvider::class,
    AdminPanelServiceProvider::class,
    ConsolePanelServiceProvider::class,
    HorizonServiceProvider::class,
    PulseServiceProvider::class,
];
