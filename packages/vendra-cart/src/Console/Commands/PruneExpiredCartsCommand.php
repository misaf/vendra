<?php

declare(strict_types=1);

namespace Misaf\VendraCart\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Misaf\VendraCart\Models\Cart;

#[Description('Delete carts whose expiry time has passed')]
#[Signature('vendra-cart:prune-expired')]
final class PruneExpiredCartsCommand extends Command
{
    public function handle(): int
    {
        $pruned = Cart::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->delete();

        $this->info("Pruned {$pruned} expired cart(s).");

        return self::SUCCESS;
    }
}
