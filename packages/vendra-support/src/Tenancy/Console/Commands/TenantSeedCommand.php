<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy\Console\Commands;

use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

use function Laravel\Prompts\search;

abstract class TenantSeedCommand extends SeedCommand
{
    private const int TENANT_SEARCH_LIMIT = 10;

    /**
     * When the application is not tenant-aware, seeding runs globally and no
     * tenant is required. Otherwise a tenant is resolved from the optional
     * argument (or prompted for interactively) and made current for the run.
     */
    protected function prepareForSeeding(): bool
    {
        if (! TenantAwareness::enabled()) {
            return true;
        }

        $tenantResolver = resolve(TenantResolver::class);
        $tenantInput = $this->resolveTenantInput($tenantResolver);

        if (! is_int($tenantInput) && ! is_string($tenantInput)) {
            $this->error('A tenant is required when a tenant provider is installed.');

            return false;
        }

        if (! $tenantResolver->makeCurrent($tenantInput)) {
            $this->error(sprintf('Tenant [%s] was not found.', $tenantInput));

            return false;
        }

        return true;
    }

    private function resolveTenantInput(TenantResolver $tenantResolver): int|string|null
    {
        $tenantInput = $this->argument('tenant');

        if (is_int($tenantInput) || is_string($tenantInput)) {
            return $tenantInput;
        }

        if (! $this->input->isInteractive()) {
            return null;
        }

        return search(
            label: 'Which tenant should receive seed data?',
            placeholder: 'Search tenant slug',
            options: fn (string $value): array => $tenantResolver->searchOptions($value, self::TENANT_SEARCH_LIMIT),
            hint: 'Seeders run only for the selected tenant.',
            scroll: self::TENANT_SEARCH_LIMIT,
        );
    }
}
