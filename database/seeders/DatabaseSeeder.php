<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Misaf\VendraConsole\Database\Seeders\ConsoleSeeder;
use Misaf\VendraSubscription\Database\Seeders\PlanSeeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
        ]);

        $this->callSilent(ConsoleSeeder::class);
    }
}
