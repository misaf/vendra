<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Observers;

use Misaf\VendraMultimedia\Models\Multimedia;
use Misaf\VendraSupport\Contracts\TenantEntitlements;
use Misaf\VendraSupport\Enums\PlanLimit;
use Misaf\VendraSupport\Exceptions\EntitlementExceededException;

/**
 * Synchronous, because `creating` aborts the write by throwing.
 */
final readonly class MultimediaObserver
{
    public function __construct(private TenantEntitlements $entitlements) {}

    /**
     * Refuse a file past the store's storage limit, whichever path uploads it.
     *
     * @throws EntitlementExceededException
     */
    public function creating(Multimedia $multimedia): void
    {
        $this->entitlements->assertCanAdd(PlanLimit::StorageMegabytesPerStore, $multimedia->size);
    }
}
