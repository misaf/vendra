<?php

declare(strict_types=1);

namespace Misaf\VendraWishlistApi\State;

use ApiPlatform\Laravel\ApiResource\ValidationError;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraWishlist\Actions\AddWishlistItemAction;
use Misaf\VendraWishlist\Models\Wishlist;
use Misaf\VendraWishlistApi\ApiResource\SavedItemResource;
use Misaf\VendraWishlistApi\ApiResource\WishlistResource;

/**
 * Save a product to the caller's default list.
 *
 * The list is resolved rather than chosen: a heart on a product card has no
 * list to pick, so the first tap creates one. The answer is the whole list so
 * the storefront can re-render every heart in a single round trip.
 *
 * @implements ProcessorInterface<SavedItemResource, WishlistResource>
 */
final readonly class SaveWishlistItemProcessor implements ProcessorInterface
{
    public function __construct(
        private AddWishlistItemAction $addWishlistItem,
        private WishlistMapper $wishlistMapper,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): WishlistResource
    {
        $user = Auth::user();

        if (! $user instanceof Model) {
            $this->reject('sellableId', __('vendra-wishlist-api::messages.sellable_not_found'));
        }

        $wishlist = Wishlist::defaultFor($user);

        $this->addWishlistItem->execute($wishlist, $this->resolveSellable($data), $data->metadata);

        return $this->wishlistMapper->map($wishlist->load('items'));
    }

    /**
     * Only real catalog rows may be saved: an unchecked identifier would let a
     * list fill up with things that never existed.
     */
    private function resolveSellable(SavedItemResource $data): Model
    {
        $product = $data->sellableType === 'product'
            ? Product::query()->find($data->sellableId)
            : null;

        if (! $product instanceof Product) {
            $this->reject('sellableId', __('vendra-wishlist-api::messages.sellable_not_found'));
        }

        return $product;
    }

    private function reject(string $property, string $message): never
    {
        throw new ValidationError(
            message: $message,
            code: $property,
            violations: [['propertyPath' => $property, 'message' => $message]],
        );
    }
}
