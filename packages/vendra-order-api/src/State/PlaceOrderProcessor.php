<?php

declare(strict_types=1);

namespace Misaf\VendraOrderApi\State;

use Illuminate\Contracts\Database\Query\Builder;
use ApiPlatform\Laravel\ApiResource\ValidationError;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Misaf\VendraAddress\Models\Address;
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraCart\Models\CartItem;
use Misaf\VendraDelivery\Actions\ScheduleDeliveryAction;
use Misaf\VendraDelivery\Data\DeliveryQuote;
use Misaf\VendraDelivery\Models\DeliverySlot;
use Misaf\VendraDelivery\Support\DeliveryZoneMatcher;
use Misaf\VendraOrder\Actions\PlaceOrderAction;
use Misaf\VendraOrder\Data\OrderLineDraft;
use Misaf\VendraOrderApi\ApiResource\CheckoutResource;
use Misaf\VendraOrderApi\ApiResource\OrderResource;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraTransaction\Models\TransactionGateway;

/**
 * Convert the authenticated customer's cart into an order.
 *
 * Prices, names and availability are read from the catalog here rather than
 * accepted from the client, so a caller cannot dictate what it pays. The same
 * holds for delivery: the fee comes from the band `misaf/vendra-delivery`
 * matches the dropped pin to, never from the request. An address that module
 * prices by hand is refused outright rather than charged a guessed fee.
 *
 * @implements ProcessorInterface<CheckoutResource, OrderResource>
 */
final readonly class PlaceOrderProcessor implements ProcessorInterface
{
    public function __construct(
        private PlaceOrderAction $placeOrder,
        private ScheduleDeliveryAction $scheduleDelivery,
        private DeliveryZoneMatcher $zoneMatcher,
        private OrderMapper $orderMapper,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): OrderResource
    {
        $user = Auth::user();

        if (! $user instanceof Model) {
            $this->reject('cartToken', __('vendra-order-api::messages.cart_not_found'));
        }

        $cart = $this->resolveCart($data->cartToken, $user);
        $currencyCode = mb_strtoupper($data->currencyCode ?? ProductPrice::defaultCurrencyCode());
        $quote = $this->resolveDeliveryQuote($data, $currencyCode);

        $order = $this->placeOrder->execute(
            cart: $cart,
            currencyCode: $currencyCode,
            lines: $this->resolveLines($cart, $currencyCode),
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
                slot: $this->resolveSlot($data->deliverySlotId),
                address: $this->resolveAddress($data->addressId, $user),
                recipientName: $data->recipientName,
                latitude: $data->latitude,
                longitude: $data->longitude,
            );
        }

        return $this->orderMapper->map($order->load('lines'));
    }

    private function resolveCart(string $token, Model $user): Cart
    {
        $cart = Cart::query()
            ->with('items')
            ->where('token', $token)
            ->where('owner_type', $user->getMorphClass())
            ->where('owner_id', $user->getKey())
            ->first();

        if (! $cart instanceof Cart || $cart->items->count() === 0) {
            $this->reject('cartToken', __('vendra-order-api::messages.cart_not_found'));
        }

        return $cart;
    }

    /**
     * @return list<OrderLineDraft>
     */
    private function resolveLines(Cart $cart, string $currencyCode): array
    {
        $lines = [];

        foreach ($cart->items as $item) {
            $product = $this->resolveProduct($item);
            $price = $product->productPrices
                ->firstWhere('currency_code', $currencyCode);

            if (! $price instanceof ProductPrice) {
                $this->reject('cartToken', __('vendra-order-api::messages.price_missing', ['product' => $product->id]));
            }

            $lines[] = new OrderLineDraft(
                sellable: $product,
                name: self::translatedName($product),
                unitAmount: (int) $price->price->getAmount(),
                quantity: $item->quantity,
                metadata: $item->metadata,
            );
        }

        return $lines;
    }

    private function resolveProduct(CartItem $item): Product
    {
        $product = $item->sellable_type === 'product'
            ? Product::query()->with('productPrices')->find($item->sellable_id)
            : null;

        if (! $product instanceof Product) {
            $this->reject('cartToken', __('vendra-order-api::messages.sellable_unsupported', ['type' => $item->sellable_type]));
        }

        if (! $product->in_stock || $product->quantity < $item->quantity) {
            $this->reject('cartToken', __('vendra-order-api::messages.out_of_stock', ['product' => $product->id]));
        }

        return $product;
    }

    private function resolveGateway(?string $slug): ?TransactionGateway
    {
        if ($slug === null || $slug === '') {
            return null;
        }

        $gateway = TransactionGateway::query()
            ->where('slug', $slug)
            ->where('active', true)
            ->first();

        if (! $gateway instanceof TransactionGateway) {
            $this->reject('gateway', __('vendra-order-api::messages.gateway_unavailable', ['gateway' => $slug]));
        }

        return $gateway;
    }

    /**
     * Price the delivery from the dropped pin.
     *
     * Without a pin there is nothing to price: the order is placed with a zero
     * delivery amount and no delivery is scheduled, which is what an in-store
     * collection or a hand-arranged address looks like.
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

        $slot = DeliverySlot::query()->where('active', true)->find($slotId);

        if (! $slot instanceof DeliverySlot) {
            $this->reject('deliverySlotId', __('vendra-order-api::messages.delivery_slot_unavailable'));
        }

        return $slot;
    }

    /**
     * Only an address on one of the caller's own profiles may be delivered to,
     * so a guessed identifier cannot address someone else's doorstep.
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
     * The product's name in every locale it has one, snapshotted onto the
     * order line so a later catalog rename never rewrites the purchase.
     *
     * @return array<string, string>
     */
    private static function translatedName(Product $product): array
    {
        $translations = [];

        foreach ($product->getTranslations('name') as $locale => $value) {
            if (is_string($locale) && is_string($value)) {
                $translations[$locale] = $value;
            }
        }

        return $translations;
    }

    /**
     * Refuse the checkout with a 422 the storefront can show against the
     * offending field, instead of a partially written order.
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
