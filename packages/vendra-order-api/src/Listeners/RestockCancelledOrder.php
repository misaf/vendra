<?php

declare(strict_types=1);

namespace Misaf\VendraOrderApi\Listeners;

use Illuminate\Database\Eloquent\Relations\Relation;
use Misaf\VendraOrder\Events\OrderCancelled;
use Misaf\VendraOrder\Models\OrderLine;
use Misaf\VendraProduct\Actions\RestockProductsAction;
use Misaf\VendraProduct\Models\Product;

/**
 * Return the stock checkout took for a cancelled order's product lines.
 *
 * Orders that never took stock are skipped, and the flag is cleared once the
 * stock is back, so it is never returned twice.
 */
final readonly class RestockCancelledOrder
{
    public function __construct(private RestockProductsAction $restockProducts) {}

    public function handle(OrderCancelled $event): void
    {
        $order = $event->order;

        if (! $order->stock_deducted) {
            return;
        }

        $quantities = [];

        $order->lines()
            ->where('sellable_type', Relation::getMorphAlias(Product::class))
            ->get()
            ->each(function (OrderLine $line) use (&$quantities): void {
                $quantities[$line->sellable_id] = ($quantities[$line->sellable_id] ?? 0) + $line->quantity;
            });

        $this->restockProducts->execute($quantities);

        $order->update(['stock_deducted' => false]);
    }
}
