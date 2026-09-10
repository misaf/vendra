<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;
use Misaf\VendraWishlist\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraWishlist\WishlistPlugin;

#[Description('Seed wishlist module data for a tenant')]
final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = WishlistPlugin::ID;

    protected $signature = self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed wishlist data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies}';

    /**
     * @return array<string, class-string>
     */
    protected function seeders(): array
    {
        return [
            'permission-policies' => PermissionPolicySeeder::class,
        ];
    }
}
