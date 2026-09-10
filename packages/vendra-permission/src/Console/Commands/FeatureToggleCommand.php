<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Laravel\Pennant\Feature;
use Misaf\VendraPermission\Enums\PermissionFeatureEnum;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

#[Description('Activate or deactivate a vendra-permission feature for a tenant')]
#[Signature('vendra-permission:feature
        {action : activate or deactivate}
        {feature : Feature case, value, short value, or all}
        {tenant : Tenant ID or slug}')]
final class FeatureToggleCommand extends Command
{
    public function handle(): int
    {
        $actionInput = $this->getStringArgument('action');

        if ($actionInput === null) {
            $this->error('Argument [action] must be a string.');

            return self::INVALID;
        }

        $action = Str::lower($actionInput);

        if (! in_array($action, ['activate', 'deactivate'], true)) {
            $this->error('Action must be one of: activate, deactivate.');

            return self::INVALID;
        }

        if (! Config::boolean('vendra-permission.features.enabled', false)) {
            $this->error('Feature resolution is disabled via vendra-permission.features.enabled.');
            $this->info('Set VENDRA_PERMISSION_FEATURES_ENABLED=true and try again.');

            return self::INVALID;
        }

        if (! TenantAwareness::enabled()) {
            $this->error('This command requires a tenant provider to be installed.');

            return self::INVALID;
        }

        $tenantInput = $this->getStringArgument('tenant');

        if ($tenantInput === null) {
            $this->error('Argument [tenant] must be a string.');

            return self::INVALID;
        }

        $tenant = $this->resolveTenant($tenantInput);

        if (! $tenant instanceof Model) {
            $this->error("Tenant [{$tenantInput}] was not found.");

            return self::FAILURE;
        }

        $featureInput = $this->getStringArgument('feature');

        if ($featureInput === null) {
            $this->error('Argument [feature] must be a string.');

            return self::INVALID;
        }

        $features = $this->resolveFeatures($featureInput);

        if ($features === []) {
            $this->error("Feature [{$featureInput}] is invalid.");
            $this->info('Accepted values:');

            foreach (PermissionFeatureEnum::cases() as $feature) {
                $this->line("- {$feature->name} ({$feature->value})");
            }

            $this->line('- all');

            return self::INVALID;
        }

        $interaction = Feature::for($tenant);

        foreach ($features as $feature) {
            if ($action === 'activate') {
                $interaction->activate($feature->value);
            } else {
                $interaction->deactivate($feature->value);
            }
        }

        $this->info(sprintf(
            'Tenant [%s] (%d): %s %d feature(s).',
            $tenant->slug,
            $tenant->id,
            $action === 'activate' ? 'activated' : 'deactivated',
            count($features),
        ));

        $this->table(
            ['Feature', 'Status'],
            array_map(
                static fn (PermissionFeatureEnum $feature): array => [
                    $feature->value,
                    $action === 'activate' ? 'active' : 'inactive',
                ],
                $features,
            ),
        );

        return self::SUCCESS;
    }

    private function resolveTenant(string $tenantInput): ?Model
    {
        return resolve(TenantResolver::class)->findByKeyOrSlug($tenantInput);
    }

    /**
     * @return array<int, PermissionFeatureEnum>
     */
    private function resolveFeatures(string $featureInput): array
    {
        $normalizedInput = Str::of($featureInput)->trim()->lower()->value();

        if ($normalizedInput === 'all') {
            return PermissionFeatureEnum::cases();
        }

        $resolved = array_values(array_filter(
            PermissionFeatureEnum::cases(),
            function (PermissionFeatureEnum $feature) use ($normalizedInput): bool {
                $normalizedCaseName = Str::lower($feature->name);
                $normalizedValue = Str::lower($feature->value);
                $shortValue = Str::lower(Str::replaceFirst('vendra-permission.', '', $feature->value));

                return $normalizedInput === $normalizedCaseName
                    || $normalizedInput === $normalizedValue
                    || $normalizedInput === $shortValue;
            }
        ));

        return $resolved;
    }

    private function getStringArgument(string $name): ?string
    {
        $argument = $this->argument($name);

        return is_string($argument) ? $argument : null;
    }
}
