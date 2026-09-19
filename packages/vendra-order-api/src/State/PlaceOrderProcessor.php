<?php

declare(strict_types=1);

namespace Misaf\VendraOrderApi\State;

use ApiPlatform\Laravel\ApiResource\ValidationError;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Misaf\VendraAddress\Models\Address;
use Misaf\VendraApi\State\Concerns\NormalizesResourceValues;
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraCart\Models\CartItem;
use Misaf\VendraDelivery\Actions\ScheduleDeliveryAction;
use Misaf\VendraDelivery\Data\DeliveryQuote;
use Misaf\VendraDelivery\Models\DeliverySlot;
use Misaf\VendraDelivery\Support\DeliverySchedule;
use Misaf\VendraDelivery\Support\DeliveryZoneMatcher;
use Misaf\VendraOrder\Actions\PlaceOrderAction;
use Misaf\VendraOrder\Data\OrderLineDraft;
use Misaf\VendraOrderApi\ApiResource\CheckoutResource;
use Misaf\VendraOrderApi\ApiResource\OrderResource;
use Misaf\VendraProduct\Actions\DeductProductStockAction;
use Misaf\VendraProduct\Data\ProductPurchaseRequest;
use Misaf\VendraProduct\Enums\ProductPurchaseRefusalEnum;
use Misaf\VendraProduct\Exceptions\InsufficientProductStockException;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraProduct\Services\ProductPurchaseQuoter;
use Misaf\VendraTransaction\Models\TransactionGateway;

/**
 * Prices and delivery fees come from the catalog and delivery bands, never the
 * client. Addresses that need a manual quote are refused. Stock is taken in
 * the same transaction, so the last unit cannot be sold twice.
 *
 * @implements ProcessorInterface<CheckoutResource, OrderResource>
 */
final readonly class PlaceOrderProcessor implements ProcessorInterface
{
    use NormalizesResourceValues;

    public function __construct(
        private PlaceOrderAction $placeOrder,
        private ScheduleDeliveryAction $scheduleDelivery,
        private DeliveryZoneMatcher $zoneMatcher,
        private DeliverySchedule $deliverySchedule,
        private OrderMapper $orderMapper,
        private ProductPurchaseQuoter $productPurchaseQuoter,
        private DeductProductStockAction $deductProductStock,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): OrderResource
    {
        $user = Auth::user();

        if (! $user instanceof Model) {
            $this->reject('cartToken', __('vendra-order-api::messages.cart_not_found'));
        }

        return DB::transaction(function () use ($data, $user): OrderResource {
            $cart = $this->resolveCart($data->cartToken, $user);
            $currencyCode = mb_strtoupper($data->currencyCode ?? ProductPrice::defaultCurrencyCode());
            $quote = $this->resolveDeliveryQuote($data, $currencyCode);

            $slot = $quote !== null ? $this->resolveSlot($data->deliverySlotId) : null;
            $address = $quote !== null ? $this->resolveAddress($data->addressId, $user) : null;

            if ($quote !== null && $data->deliveryDate !== null && ! $this->deliverySchedule->isBookable($data->deliveryDate)) {
                $this->reject('deliveryDate', __('vendra-order-api::messages.delivery_date_unavailable'));
            }

            $lines = $this->resolveLines($cart, $currencyCode);

            $this->deductStock($lines);

            $order = $this->placeOrder->execute(
                cart: $cart,
                currencyCode: $currencyCode,
                lines: $lines,
                customer: $user,
                deliveryAmount: $quote instanceof DeliveryQuote ? $quote->feeAmount : 0,
                cardMessage: $data->cardMessage,
                transactionGateway: $this->resolveGateway($data->gateway),
                paymentReference: $data->paymentReference,
            );

            if ($quote !== null) {
                $this->scheduleDelivery->execute(
                    order: $order,
                    quote: $quote,
                    scheduledFor: $data->deliveryDate,
                    slot: $slot,
                    address: $address,
                    recipientName: $data->recipientName,
                    latitude: $data->latitude,
                    longitude: $data->longitude,
                );
            }

            return $this->orderMapper->map($order->load('lines'));
        }, attempts: 3);
    }

    private function resolveCart(string $token, Model $user): Cart
    {
        $cart = Cart::query()
            ->where('token', $token)
            ->where('owner_type', $user->getMorphClass())
            ->where('owner_id', $user->getKey())
            ->lockForUpdate()
            ->first();

        if ($cart instanceof Cart) {
            $cart->setRelation('items', $cart->items()->lockForUpdate()->get());
        }

        if (! $cart instanceof Cart || $cart->items->count() === 0) {
            $this->reject('cartToken', __('vendra-order-api::messages.cart_not_found'));
        }

        return $cart;
    }

    /**
     * Price every cart item from one catalog query, refusing any item that cannot be bought.
     *
     * @return list<OrderLineDraft>
     */
    private function resolveLines(Cart $cart, string $currencyCode): array
    {
        $productMorphAlias = Relation::getMorphAlias(Product::class);

        $quotes = $this->productPurchaseQuoter->quote(
            $cart->items
                ->filter(fn (CartItem $item): bool => $item->sellable_type === $productMorphAlias)
                ->map(fn (CartItem $item): ProductPurchaseRequest => new ProductPurchaseRequest($item->sellable_id, $item->quantity))
                ->all(),
            $currencyCode,
        );

        $lines = [];

        foreach ($cart->items as $key => $item) {
            $quote = $quotes[$key] ?? ProductPurchaseRefusalEnum::Unavailable;

            if ($quote instanceof ProductPurchaseRefusalEnum) {
                $this->rejectItem($item, $quote);
            }

            $lines[] = new OrderLineDraft(
                sellable: $quote->product,
                name: $this->normalizeTranslations($quote->product->getTranslations('name')),
                unitAmount: $quote->unitAmount,
                quantity: $item->quantity,
                metadata: $item->metadata,
            );
        }

        return $lines;
    }

    /**
     * Take the ordered quantities off the products.
     *
     * @param  list<OrderLineDraft>  $lines
     */
    private function deductStock(array $lines): void
    {
        $quantities = [];

        foreach ($lines as $line) {
            $productId = (int) $line->sellable->getKey();
            $quantities[$productId] = ($quantities[$productId] ?? 0) + $line->quantity;
        }

        try {
            $this->deductProductStock->execute($quantities);
        } catch (InsufficientProductStockException $exception) {
            $this->reject('cartToken', __('vendra-order-api::messages.out_of_stock', ['product' => $exception->productId]));
        }
    }

    private function rejectItem(CartItem $item, ProductPurchaseRefusalEnum $refusal): never
    {
        $this->reject('cartToken', match ($refusal) {
            ProductPurchaseRefusalEnum::Unavailable => __('vendra-order-api::messages.sellable_unsupported', ['type' => $item->sellable_type]),
            ProductPurchaseRefusalEnum::OutOfStock => __('vendra-order-api::messages.out_of_stock', ['product' => $item->sellable_id]),
            ProductPurchaseRefusalEnum::PriceMissing => __('vendra-order-api::messages.price_missing', ['product' => $item->sellable_id]),
        });
    }

    private function resolveGateway(?string $slug): ?TransactionGateway
    {
        if ($slug === null || $slug === '') {
            return null;
        }

        $gateway = TransactionGateway::query()
            ->where('slug', $slug)
            ->active()
            ->first();

        if (! $gateway instanceof TransactionGateway) {
            $this->reject('gateway', __('vendra-order-api::messages.gateway_unavailable', ['gateway' => $slug]));
        }

        return $gateway;
    }

    /**
     * Price the delivery from the dropped pin; without one, nothing is delivered.
     */
    private function resolveDeliveryQuote(CheckoutResource $data, string $currencyCode): ?DeliveryQuote
    {
        if ($data->latitude === null || $data->longitude === null) {
            return null;
        }

        $quote = $this->zoneMatcher->quoteFor($data->latitude, $data->longitude, $currencyCode);

        if (! $quote->isDeliverable()) {
            $this->reject('latitude', __('vendra-order-api::messages.delivery_out_of_range'));
        }

        return $quote;
    }

    private function resolveSlot(?int $slotId): ?DeliverySlot
    {
        if ($slotId === null) {
            return null;
        }

        $slot = DeliverySlot::query()->active()->find($slotId);

        if (! $slot instanceof DeliverySlot) {
            $this->reject('deliverySlotId', __('vendra-order-api::messages.delivery_slot_unavailable'));
        }

        return $slot;
    }

    /**
     * Resolve an address from the caller's own profiles only.
     */
    private function resolveAddress(?int $addressId, Model $user): ?Address
    {
        if ($addressId === null) {
            return null;
        }

        $address = Address::query()
            ->whereHas('userProfile', fn (Builder $query) => $query->where('user_id', $user->getKey()))
            ->find($addressId);

        if (! $address instanceof Address) {
            $this->reject('addressId', __('vendra-order-api::messages.address_not_found'));
        }

        return $address;
    }

    /**
     * Refuse the checkout with a 422 error on the given field.
     */
    private function reject(string $property, string $message): never
    {
        throw new ValidationError(
            message: $message,
            code: $property,
            violations: [['propertyPath' => $property, 'message' => $message]],
        );
    }
}
