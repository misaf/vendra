<?php

declare(strict_types=1);

namespace Misaf\VendraWishlistApi\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\McpTool;
use ApiPlatform\Metadata\Post;
use Misaf\VendraWishlistApi\Http\Requests\SaveWishlistItemRequest;
use Misaf\VendraWishlistApi\State\ForgetWishlistItemProcessor;
use Misaf\VendraWishlistApi\State\SaveWishlistItemProcessor;
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * The heart button on a product card.
 *
 * Saving writes to the caller's default list, creating it on first use, and
 * answers with the whole list so the storefront can re-render every heart in
 * one round trip.
 */
#[ApiResource(
    shortName: 'SavedItem',
    operations: [
        new Post(
            uriTemplate: '/customers/saved-items',
            status: 200,
            processor: SaveWishlistItemProcessor::class,
            rules: SaveWishlistItemRequest::class,
            middleware: ['auth:sanctum', 'throttle:60,1'],
        ),
        new Delete(
            uriTemplate: '/customers/saved-items/{id}',
            status: 204,
            output: false,
            processor: ForgetWishlistItemProcessor::class,
            middleware: ['auth:sanctum', 'throttle:60,1'],
            read: false,
        ),
    ],
    mcp: [
        'save_wishlist_item' => new McpTool(
            description: "Save a product to the authenticated user's default wishlist.",
            input: self::class,
            processor: SaveWishlistItemProcessor::class,
            validate: true,
            rules: SaveWishlistItemRequest::RULES,
        ),
    ],
)]
final class SavedItemResource
{
    #[SerializedName('sellableType')]
    public string $sellableType = 'product';

    #[SerializedName('sellableId')]
    public int $sellableId = 0;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $metadata = null;
}
