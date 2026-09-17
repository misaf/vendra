<?php

declare(strict_types=1);

namespace Misaf\VendraAddress\Database\Seeders;

use Misaf\VendraAddress\Enums\AddressPolicyEnum;
use Misaf\VendraSupport\Tenancy\Database\Seeders\PermissionPolicySeeder as BasePermissionPolicySeeder;

final class PermissionPolicySeeder extends BasePermissionPolicySeeder
{
    protected const string MODULE_NAME = 'vendra-address';

    /**
     * @return list<string>
     */
    protected function policies(): array
    {
        return array_column(AddressPolicyEnum::cases(), 'value');
    }
}
