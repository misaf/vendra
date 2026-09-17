<?php

declare(strict_types=1);

namespace Misaf\VendraPhone\Database\Seeders;

use Misaf\VendraPhone\Enums\PhoneNumberPolicyEnum;
use Misaf\VendraSupport\Tenancy\Database\Seeders\PermissionPolicySeeder as BasePermissionPolicySeeder;

final class PermissionPolicySeeder extends BasePermissionPolicySeeder
{
    protected const string MODULE_NAME = 'vendra-phone';

    /**
     * @return list<string>
     */
    protected function policies(): array
    {
        return array_column(PhoneNumberPolicyEnum::cases(), 'value');
    }
}
