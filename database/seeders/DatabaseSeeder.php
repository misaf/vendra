<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Misaf\VendraCurrency\Database\Seeders\DatabaseSeeder as CurrencySeeder;
use Misaf\VendraNewsletter\Database\Seeders\NewsletterSeeder;
use Misaf\VendraPermission\Database\Seeders\PermissionSeeder;
use Misaf\VendraPermission\Database\Seeders\RoleAssignmentSeeder;
use Misaf\VendraTenant\Database\Seeders\TenantSeeder;
use Misaf\VendraUser\Database\Seeders\UserSeeder;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * @return void
     */
    public function run(): void
    {
        $this->call([
            TenantSeeder::class,
            CurrencySeeder::class,
            // PermissionSeeder::class,
            UserSeeder::class,
            // RoleAssignmentSeeder::class,
            SettingsSeeder::class,
            // NewsletterSeeder::class,
        ]);
    }
}
