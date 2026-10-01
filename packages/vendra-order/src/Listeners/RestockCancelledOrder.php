<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Listeners;

use LogicException;
use Misaf\VendraOrder\Events\OrderCancelled;
use Misaf\VendraOrder\Models\OrderLine;
use Misaf\VendraSupport\Contracts\StockRestorer;

/**
 * Return the stock checkout took for a cancelled order's product lines.
 *
 * Orders that never took stock are skipped, and the flag is cleared once the
 * stock is back, so it is never returned twice.
 */
final readonly class RestockCancelledOrder
{
    public function __construct(private StockRestorer $stockRestorer) {}

    public function handle(OrderCancelled $event): void
    {
        $order = $event->order;

        if (! $order->stock_deducted) {
            return;
        }

        $sellableType = $this->stockRestorer->sellableType();

        throw_if($sellableType === null, LogicException::class, 'Install a stock provider before cancelling an order with deducted stock.');

        $quantities = [];

        $order->lines()
            ->where('sellable_type', $sellableType)
            ->get()
            ->each(function (OrderLine $line) use (&$quantities): void {
                $quantities[$line->sellable_id] = ($quantities[$line->sellable_id] ?? 0) + $line->quantity;
            });

        $this->stockRestorer->restore($quantities);

        $order->update(['stock_deducted' => false]);
    }
}
