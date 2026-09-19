<?php

declare(strict_types=1);

namespace Misaf\VendraWishlistApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Misaf\VendraWishlist\Models\WishlistItem;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Remove a saved item, looked up through the caller's own lists.
 *
 * @implements ProcessorInterface<mixed, void>
 */
final readonly class ForgetWishlistItemProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $user = Auth::user();
        $itemId = Arr::get($uriVariables, 'id', null);

        throw_if(! $user instanceof Model || ! is_numeric($itemId), NotFoundHttpException::class);

        $item = WishlistItem::query()
            ->whereHas('wishlist', function (Builder $query) use ($user): void {
                $query
                    ->where('owner_type', $user->getMorphClass())
                    ->where('owner_id', $user->getKey());
            })
            ->find((int) $itemId);

        throw_unless($item instanceof WishlistItem, NotFoundHttpException::class);

        $item->delete();
    }
}
