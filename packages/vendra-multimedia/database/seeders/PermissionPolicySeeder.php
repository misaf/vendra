<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Database\Seeders;

use Misaf\VendraMultimedia\Enums\MultimediaPolicyEnum;
use Misaf\VendraMultimedia\MultimediaPlugin;
use Misaf\VendraSupport\Tenancy\Database\Seeders\PermissionPolicySeeder as BasePermissionPolicySeeder;

final class PermissionPolicySeeder extends BasePermissionPolicySeeder
{
    protected const string MODULE_NAME = MultimediaPlugin::ID;

    /**
     * @return list<string>
     */
    protected function policies(): array
    {
        return array_column(MultimediaPolicyEnum::cases(), 'value');
    }
}
