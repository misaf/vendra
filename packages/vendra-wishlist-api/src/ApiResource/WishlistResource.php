<?php

declare(strict_types=1);

namespace Misaf\VendraWishlistApi\ApiResource;

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
use Misaf\VendraWishlist\Models\Wishlist;
use Misaf\VendraWishlistApi\State\WishlistLinksHandler;
use Misaf\VendraWishlistApi\State\WishlistMapper;

#[ApiResource(
    shortName: 'Wishlist',
    provider: EloquentResourceProvider::class,
    stateOptions: new EloquentResourceOptions(
        modelClass: Wishlist::class,
        handleLinks: WishlistLinksHandler::class,
        mapper: WishlistMapper::class,
    ),
    mcp: [
        'get_wishlist' => new McpTool(
            description: 'Get one wishlist owned by the authenticated user.',
            input: McpResourceIdentifierInput::class,
            provider: EloquentResourceProvider::class,
            policy: 'view',
        ),
        'list_wishlists' => new McpToolCollection(
            description: 'List wishlists owned by the authenticated user.',
            input: McpCollectionInput::class,
            provider: EloquentResourceProvider::class,
            policy: 'viewAny',
        ),
    ],
)]
#[Get(
    uriTemplate: '/customers/wishlists/{id}',
    policy: 'view',
    middleware: 'auth:sanctum',
)]
#[GetCollection(
    uriTemplate: '/customers/wishlists',
    policy: 'viewAny',
    middleware: 'auth:sanctum',
)]
final readonly class WishlistResource
{
    /**
     * @param  array<int, SavedItem>  $items
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The wishlist unique identifier')]
        public int $id,
        public string $token,
        public string $name,
        public bool $isDefault,
        public array $items,
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
