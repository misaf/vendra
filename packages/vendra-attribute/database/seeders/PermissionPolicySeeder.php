<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Database\Seeders;

use Misaf\VendraAttribute\AttributePlugin;
use Misaf\VendraAttribute\Enums\AttributePolicyEnum;
use Misaf\VendraAttribute\Enums\AttributeValuePolicyEnum;
use Misaf\VendraSupport\Tenancy\Database\Seeders\PermissionPolicySeeder as BasePermissionPolicySeeder;

final class PermissionPolicySeeder extends BasePermissionPolicySeeder
{
    protected const string MODULE_NAME = AttributePlugin::ID;

    /**
     * @return list<string>
     */
    protected function policies(): array
    {
        return [
            ...array_column(AttributePolicyEnum::cases(), 'value'),
            ...array_column(AttributeValuePolicyEnum::cases(), 'value'),
        ];
    }
}
