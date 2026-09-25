<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Database\Seeders;

use Illuminate\Database\Seeder;
use Misaf\VendraCurrency\Actions\InstallCurrenciesAction;

/**
 * Install the tenantless currency the seeded plans are priced in. Run it
 * outside a tenant, where the install action writes platform currencies.
 */
final class PlatformCurrencySeeder extends Seeder
{
    public function __construct(private readonly InstallCurrenciesAction $installCurrencies) {}

    public function run(): void
    {
        $this->installCurrencies->execute(['USD']);
    }
}
