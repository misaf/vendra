<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use Misaf\VendraCart\CartPlugin;
use Misaf\VendraCart\Filament\Clusters\Resources\Carts\CartResource;
use Misaf\VendraCart\Filament\Clusters\Resources\Carts\RelationManagers\CartItemsRelationManager;
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraCart\Policies\CartPolicy;
use Misaf\VendraSupport\Filament\Clusters\SalesCluster;

it('registers a cart plugin and sales resource', function (): void {
    expect(CartPlugin::make()->getId())->toBe('vendra-cart')
        ->and(CartResource::getModel())->toBe(Cart::class)
        ->and(CartResource::getCluster())->toBe(SalesCluster::class)
        ->and(Gate::getPolicyFor(Cart::class))->toBeInstanceOf(CartPolicy::class)
        ->and(CartResource::getRelations())->toContain(CartItemsRelationManager::class)
        ->and(CartItemsRelationManager::isBadgeDeferred(new Cart, ''))->toBeTrue()
        ->and(CartResource::getPages())->toHaveKeys(['index', 'view']);
});
