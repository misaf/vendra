<?php

declare(strict_types=1);

namespace Misaf\VendraDocument\Database\Seeders;

use Misaf\VendraDocument\Enums\DocumentPolicyEnum;
use Misaf\VendraSupport\Tenancy\Database\Seeders\PermissionPolicySeeder as BasePermissionPolicySeeder;

final class PermissionPolicySeeder extends BasePermissionPolicySeeder
{
    protected const string MODULE_NAME = 'vendra-document';

    /**
     * @return list<string>
     */
    protected function policies(): array
    {
        return array_column(DocumentPolicyEnum::cases(), 'value');
    }
}
