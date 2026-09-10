<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraOrder\Data\OrderLineDraft;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraTransaction\Models\TransactionGateway;

final class PlaceOrderAction
{
    /**
     * Convert a cart into an order.
     *
     * Pricing stays with the caller: the catalog is not this module's concern,
     * so priced drafts come in and an immutable snapshot goes out. Creating the
     * order, its lines and clearing the cart are several writes, so the whole
     * conversion runs in one transaction — a half-converted cart would let the
     * customer pay for lines the order never recorded.
     *
     * The cart row itself survives so its token stays usable for the next
     * order; only its items are cleared.
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

        Validator::make([
            'currency_code' => $currencyCode,
            'lines' => $lines,
            'delivery_amount' => $deliveryAmount,
            'payment_reference' => $paymentReference,
            'quantities' => array_map(fn (OrderLineDraft $line): int => $line->quantity, $lines),
            'unit_amounts' => array_map(fn (OrderLineDraft $line): int => $line->unitAmount, $lines),
        ], [
            'currency_code' => ['required', 'string', 'size:3'],
            'lines' => ['required', 'array', 'min:1'],
            'delivery_amount' => ['integer', 'min:0'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'quantities.*' => ['integer', 'min:1'],
            'unit_amounts.*' => ['integer', 'min:0'],
        ])->validate();

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
