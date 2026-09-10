<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraTransaction\Models\TransactionGateway;

final class SetDefaultTransactionGatewayAction
{
    public function execute(TransactionGateway $gateway): void
    {
        DB::transaction(function () use ($gateway): void {
            TransactionGateway::query()
                ->lockForUpdate()
                ->get(['id']);

            $gateway->update([
                'active' => true,
                'is_default' => true,
            ]);
        });
    }
}
