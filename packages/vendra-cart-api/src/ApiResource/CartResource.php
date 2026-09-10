<?php

declare(strict_types=1);

namespace Misaf\VendraCartApi\ApiResource;

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
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraCartApi\State\CartLinksHandler;
use Misaf\VendraCartApi\State\CartMapper;

#[ApiResource(
    shortName: 'Cart',
    provider: EloquentResourceProvider::class,
    stateOptions: new EloquentResourceOptions(
        modelClass: Cart::class,
        handleLinks: CartLinksHandler::class,
        mapper: CartMapper::class,
    ),
    mcp: [
        'get_cart' => new McpTool(
            description: 'Get one cart owned by the authenticated user.',
            input: McpResourceIdentifierInput::class,
            provider: EloquentResourceProvider::class,
            policy: 'view',
        ),
        'list_carts' => new McpToolCollection(
            description: 'List carts owned by the authenticated user.',
            input: McpCollectionInput::class,
            provider: EloquentResourceProvider::class,
            policy: 'viewAny',
        ),
    ],
)]
#[Get(
    uriTemplate: '/sales/carts/{id}',
    policy: 'view',
    middleware: 'auth:sanctum',
)]
#[GetCollection(
    uriTemplate: '/sales/carts',
    policy: 'viewAny',
    middleware: 'auth:sanctum',
)]
final readonly class CartResource
{
    /**
     * @param  array<int, CartLine>  $lines
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The cart unique identifier')]
        public int $id,
        public string $token,
        public ?string $expiresAt,
        public array $lines,
        private ?string $ownerType,
        private ?int $ownerId,
    ) {}

    #[ApiProperty(readable: false, writable: false)]
    public function isOwnedBy(Authenticatable $user): bool
    {
        return $user instanceof Model
            && $this->ownerType === $user->getMorphClass()
            && $this->ownerId === $user->getAuthIdentifier();
    }
}
