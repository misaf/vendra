<?php

declare(strict_types=1);

namespace Misaf\VendraOrderApi\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\McpTool;
use ApiPlatform\Metadata\Post;
use Misaf\VendraOrderApi\Http\Requests\PlaceOrderRequest;
use Misaf\VendraOrderApi\State\PlaceOrderProcessor;
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * Checkout input. Prices are never accepted from the client: the processor
 * reads them from the catalog and snapshots them onto the order.
 */
#[ApiResource(
    shortName: 'Checkout',
    operations: [
        new Post(
            uriTemplate: '/sales/checkout',
            status: 201,
            processor: PlaceOrderProcessor::class,
            rules: PlaceOrderRequest::class,
            middleware: ['auth:sanctum', 'throttle:20,1'],
        ),
    ],
    mcp: [
        'place_order' => new McpTool(
            description: "Convert the authenticated user's cart into an order.",
            input: self::class,
            processor: PlaceOrderProcessor::class,
            validate: true,
            rules: PlaceOrderRequest::RULES,
        ),
    ],
)]
final class CheckoutResource
{
    /**
     * The configured name converter maps camelCase wire names onto snake_case
     * PHP properties, so every multi-word input carries an explicit serialized
     * name and stays camelCase on both sides.
     */
    #[SerializedName('cartToken')]
    public string $cartToken = '';

    #[SerializedName('currencyCode')]
    public ?string $currencyCode = null;

    public ?string $gateway = null;

    #[SerializedName('paymentReference')]
    public ?string $paymentReference = null;

    #[SerializedName('cardMessage')]
    public ?string $cardMessage = null;

    #[SerializedName('recipientName')]
    public ?string $recipientName = null;

    #[SerializedName('addressId')]
    public ?int $addressId = null;

    public ?float $latitude = null;

    public ?float $longitude = null;

    #[SerializedName('deliveryDate')]
    public ?string $deliveryDate = null;

    #[SerializedName('deliverySlotId')]
    public ?int $deliverySlotId = null;
}
