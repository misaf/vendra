<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Features;

use Illuminate\Support\Facades\Config;
use Laravel\Pennant\Attributes\Name;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraUserProfile\Enums\UserProfileFeatureEnum;

#[Name(UserProfileFeatureEnum::ModuleEnabled->value)]
final class ModuleEnabled
{
    public function before(mixed $scope): ?bool
    {
        if (TenantAwareness::enabled()) {
            $tenantModel = resolve(TenantResolver::class)->modelClass();

            if (! $scope instanceof $tenantModel) {
                return false;
            }
        }

        if (! Config::boolean('vendra-user-profile.features_enabled', true)) {
            return false;
        }

        return Config::boolean('vendra-user-profile.module_enabled', true) ? true : null;
    }

    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
