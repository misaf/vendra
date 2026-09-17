<?php

declare(strict_types=1);

namespace Misaf\VendraVerification\Database\Seeders;

use Misaf\VendraSupport\Tenancy\Database\Seeders\PermissionPolicySeeder as BasePermissionPolicySeeder;
use Misaf\VendraVerification\Enums\VerificationPolicyEnum;

final class PermissionPolicySeeder extends BasePermissionPolicySeeder
{
    protected const string MODULE_NAME = 'vendra-verification';

    /**
     * @return list<string>
     */
    protected function policies(): array
    {
        return array_column(VerificationPolicyEnum::cases(), 'value');
    }
}
