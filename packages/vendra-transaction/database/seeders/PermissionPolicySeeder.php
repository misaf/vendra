<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Database\Seeders;

use Misaf\VendraSupport\Tenancy\Database\Seeders\PermissionPolicySeeder as BasePermissionPolicySeeder;
use Misaf\VendraTransaction\Enums\TransactionGatewayPolicyEnum;
use Misaf\VendraTransaction\Enums\TransactionPolicyEnum;
use Misaf\VendraTransaction\Enums\WalletPolicyEnum;

final class PermissionPolicySeeder extends BasePermissionPolicySeeder
{
    protected const string MODULE_NAME = 'vendra-transaction';

    /**
     * @return list<string>
     */
    protected function policies(): array
    {
        return [
            ...array_column(TransactionGatewayPolicyEnum::cases(), 'value'),
            ...array_column(TransactionPolicyEnum::cases(), 'value'),
            ...array_column(WalletPolicyEnum::cases(), 'value'),
        ];
    }
}
