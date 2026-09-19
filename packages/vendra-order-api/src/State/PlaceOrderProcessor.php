<?php

declare(strict_types=1);

namespace Misaf\VendraOrderApi\State;

use ApiPlatform\Laravel\ApiResource\ValidationError;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Misaf\VendraAddress\Models\Address;
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
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraTransaction\Models\TransactionGateway;

/**
 * Prices and delivery fees come from the catalog and delivery bands, never the
 * client. Addresses that need a manual quote are refused.
 *
 * @implements ProcessorInterface<CheckoutResource, OrderResource>
 */
final readonly class PlaceOrderProcessor implements ProcessorInterface
{
    public function __construct(
        private PlaceOrderAction $placeOrder,
        private ScheduleDeliveryAction $scheduleDelivery,
        private DeliveryZoneMatcher $zoneMatcher,
        private DeliverySchedule $deliverySchedule,
        private OrderMapper $orderMapper,
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
            ? Product::query()
                ->with('productPrices')
                ->whereHas('productCategory', fn (Builder $query) => $query->where('active', true))
                ->find($item->sellable_id)
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

        $slot = DeliverySlot::query()->where('active', true)->find($slotId);

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
     * Get the product's name in every locale, to snapshot onto the order line.
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
