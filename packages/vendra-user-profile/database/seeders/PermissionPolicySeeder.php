<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Database\Seeders;

use Misaf\VendraSupport\Tenancy\Database\Seeders\PermissionPolicySeeder as BasePermissionPolicySeeder;
use Misaf\VendraUserProfile\Enums\UserProfilePolicyEnum;

final class PermissionPolicySeeder extends BasePermissionPolicySeeder
{
    protected const string MODULE_NAME = 'vendra-user-profile';

    /**
     * @return list<string>
     */
    protected function policies(): array
    {
        return array_column(UserProfilePolicyEnum::cases(), 'value');
    }
}
