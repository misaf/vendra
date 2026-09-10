<?php

declare(strict_types=1);

namespace Misaf\VendraOrderApi\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\McpTool;
use ApiPlatform\Metadata\McpToolCollection;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraApi\ApiResource\McpCollectionInput;
use Misaf\VendraApi\ApiResource\McpResourceIdentifierInput;
use Misaf\VendraApi\State\EloquentResourceOptions;
use Misaf\VendraApi\State\EloquentResourceProvider;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraOrderApi\State\OrderLinksHandler;
use Misaf\VendraOrderApi\State\OrderMapper;

#[ApiResource(
    shortName: 'Order',
    provider: EloquentResourceProvider::class,
    stateOptions: new EloquentResourceOptions(
        modelClass: Order::class,
        handleLinks: OrderLinksHandler::class,
        mapper: OrderMapper::class,
    ),
    mcp: [
        'get_order' => new McpTool(
            description: 'Get one order placed by the authenticated user.',
            input: McpResourceIdentifierInput::class,
            provider: EloquentResourceProvider::class,
            policy: 'view',
        ),
        'list_orders' => new McpToolCollection(
            description: 'List orders placed by the authenticated user.',
            input: McpCollectionInput::class,
            provider: EloquentResourceProvider::class,
            policy: 'viewAny',
        ),
    ],
)]
#[Get(
    uriTemplate: '/sales/orders/{id}',
    policy: 'view',
    middleware: 'auth:sanctum',
)]
#[GetCollection(
    uriTemplate: '/sales/orders',
    policy: 'viewAny',
    middleware: 'auth:sanctum',
)]
final readonly class OrderResource
{
    /**
     * @param  array<int, OrderLine>  $lines
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The order unique identifier')]
        public int $id,
        public string $number,
        public string $status,
        public string $currencyCode,
        public int $itemsAmount,
        public int $deliveryAmount,
        public int $totalAmount,
        public ?string $paymentReference,
        public ?string $cardMessage,
        public ?string $placedAt,
        public array $lines,
        private ?string $customerType,
        private ?int $customerId,
    ) {}

    #[ApiProperty(readable: false, writable: false)]
    public function isOwnedBy(Authenticatable $user): bool
    {
        return $user instanceof Model
            && $this->customerType === $user->getMorphClass()
            && $this->customerId === $user->getAuthIdentifier();
    }
}
