<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Observers;

use RuntimeException;

final class LedgerEntryObserver
{
    public function updating(): never
    {
        throw new RuntimeException('Ledger entries are immutable and cannot be updated.');
    }

    public function deleting(): never
    {
        throw new RuntimeException('Ledger entries are immutable and cannot be deleted.');
    }
}
