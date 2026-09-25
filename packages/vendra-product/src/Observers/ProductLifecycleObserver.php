<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Observers;

use Misaf\VendraProduct\Models\Product;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;
use Misaf\VendraSupport\Contracts\TenantEntitlements;
use Misaf\VendraSupport\Enums\PlanLimit;
use Misaf\VendraSupport\Exceptions\EntitlementExceededException;

/**
 * The synchronous product hooks that cannot run on the queue.
 *
 * `creating` aborts by throwing, `updated` reads `wasChanged()`, and
 * `forceDeleting` needs the pivot rows.
 */
final readonly class ProductLifecycleObserver
{
    public function __construct(private TenantEntitlements $entitlements) {}

    /**
     * Refuse a product past the store's plan limit, whichever path creates it.
     *
     * The count is not locked, so two concurrent creates may pass one over the limit.
     *
     * @throws EntitlementExceededException
     */
    public function creating(Product $product): void
    {
        $this->entitlements->assertCanAdd(PlanLimit::ProductsPerStore);
    }

    public function updated(Product $product): void
    {
        if ($product->wasChanged('product_category_id')) {
            $product->detachStaleAttributeValueSelections();
        }
    }

    public function forceDeleting(Product $product): void
    {
        if (AttributeIntegration::valueModel() !== null) {
            $product->selectedAttributeValues()->detach();
        }
    }
}
