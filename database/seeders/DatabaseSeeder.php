<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Misaf\VendraConsole\Database\Seeders\ConsoleSeeder;
use Misaf\VendraCurrency\Database\Seeders\PlatformCurrencySeeder;
use Misaf\VendraSubscription\Database\Seeders\PlanSeeder;
use Misaf\VendraTransaction\Database\Seeders\PlatformGatewaySeeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlatformCurrencySeeder::class,
            PlanSeeder::class,
            PlatformGatewaySeeder::class,
        ]);

        $this->callSilent(ConsoleSeeder::class);
    }
}
