<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraOrder\Data\OrderLineDraft;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraTransaction\Models\TransactionGateway;
use RuntimeException;

final class PlaceOrderAction
{
    /**
     * The cart's items are cleared but the cart is kept, so its token stays
     * usable. The caller validates the currency, lines, and amounts first.
     *
     * @param  list<OrderLineDraft>  $lines
     */
    public function execute(
        Cart $cart,
        string $currencyCode,
        array $lines,
        ?Model $customer = null,
        int $deliveryAmount = 0,
        ?string $cardMessage = null,
        ?TransactionGateway $transactionGateway = null,
        ?string $paymentReference = null,
    ): Order {
        $itemsAmount = 0;

        foreach ($lines as $line) {
            $itemsAmount += $line->lineAmount();
        }

        return DB::transaction(function () use (
            $cart,
            $currencyCode,
            $lines,
            $customer,
            $itemsAmount,
            $deliveryAmount,
            $cardMessage,
            $transactionGateway,
            $paymentReference,
        ): Order {
            $cart->refreshForUpdate();

            throw_if($cart->items()->lockForUpdate()->first() === null, RuntimeException::class, 'Cannot place an order from an empty or already checked out cart.');

            $order = Order::query()->create([
                'customer_type' => $customer?->getMorphClass(),
                'customer_id' => $customer?->getKey(),
                'cart_id' => $cart->getKey(),
                'transaction_gateway_id' => $transactionGateway?->getKey(),
                'currency_code' => $currencyCode,
                'items_amount' => $itemsAmount,
                'delivery_amount' => $deliveryAmount,
                'total_amount' => $itemsAmount + $deliveryAmount,
                'payment_reference' => $paymentReference,
                'card_message' => $cardMessage,
                'placed_at' => now(),
            ]);

            foreach ($lines as $line) {
                $order->lines()->create($line->toAttributes($currencyCode));
            }

            $cart->items()->delete();

            return $order;
        }, attempts: 3);
    }
}
