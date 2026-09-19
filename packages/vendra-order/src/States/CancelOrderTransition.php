<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\States;

use Illuminate\Support\Facades\DB;
use Misaf\VendraOrder\Events\OrderCancelled;
use Misaf\VendraOrder\Models\Order;
use Spatie\ModelStates\Exceptions\TransitionNotFound;
use Spatie\ModelStates\Transition;

/**
 * Every cancellation runs here, including a direct `Order::cancel()`, so
 * OrderCancelled is always dispatched and the stock always comes back.
 */
final class CancelOrderTransition extends Transition
{
    public function __construct(private readonly Order $order) {}

    public function handle(): Order
    {
        return DB::transaction(function (): Order {
            /*
             | Re-read under a row lock: the transition was validated against an
             | in-memory status, and two stale copies cancelling the same order
             | would otherwise both return its stock.
             */
            $this->order->refreshForUpdate();

            throw_unless(
                $this->order->status->canTransitionTo(Cancelled::class),
                TransitionNotFound::make($this->order->status::class, Cancelled::class, Order::class),
            );

            $this->order->status = new Cancelled($this->order);
            $this->order->save();

            event(new OrderCancelled($this->order));

            return $this->order;
        });
    }
}
