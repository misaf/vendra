<?php

declare(strict_types=1);

namespace Misaf\VendraOrderApi\State;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraApi\State\ResourceMapper;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraOrder\Models\OrderLine as OrderLineModel;
use Misaf\VendraOrderApi\ApiResource\OrderLine;
use Misaf\VendraOrderApi\ApiResource\OrderResource;
use UnexpectedValueException;

final class OrderMapper implements ResourceMapper
{
    public function map(Model $model): OrderResource
    {
        throw_unless($model instanceof Order, UnexpectedValueException::class, 'Expected an order model.');

        return new OrderResource(
            id: $model->id,
            number: $model->number,
            status: $model->status::getMorphClass(),
            currencyCode: $model->currency_code,
            itemsAmount: (int) $model->items_amount->getAmount(),
            deliveryAmount: (int) $model->delivery_amount->getAmount(),
            totalAmount: (int) $model->total_amount->getAmount(),
            paymentReference: $model->payment_reference,
            cardMessage: $model->card_message,
            placedAt: $model->placed_at?->toAtomString(),
            lines: $model->lines
                ->map(fn (OrderLineModel $line): OrderLine => new OrderLine(
                    id: $line->id,
                    sellableType: $line->sellable_type,
                    sellableId: $line->sellable_id,
                    name: self::localizedName($line),
                    quantity: $line->quantity,
                    unitAmount: (int) $line->unit_amount->getAmount(),
                    lineAmount: (int) $line->line_amount->getAmount(),
                    metadata: $line->metadata,
                ))
                ->all(),
            customerType: $model->customer_type,
            customerId: $model->customer_id,
        );
    }

    /**
     * The line's name snapshot in the active locale, falling back to whatever
     * the order was placed with.
     */
    private static function localizedName(OrderLineModel $line): string
    {
        $name = $line->getTranslation('name', app()->getLocale());

        return is_string($name) ? $name : '';
    }
}
