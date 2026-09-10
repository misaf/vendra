<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Misaf\VendraStore\Models\Store;

final class OffboardStoreAction
{
    public const int MAX_REASON_LENGTH = 2000;

    public function execute(Store $store, string $reason): Store
    {
        $reason = mb_trim($reason);

        if ($reason === '') {
            throw new InvalidArgumentException('An offboarding reason is required.');
        }

        if (Str::length($reason) > self::MAX_REASON_LENGTH) {
            throw new InvalidArgumentException('The offboarding reason may not exceed '.self::MAX_REASON_LENGTH.' characters.');
        }

        return DB::transaction(function () use ($store, $reason): Store {
            $lockedStore = Store::query()
                ->withTrashed()
                ->whereKey($store->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedStore->trashed()) {
                return $lockedStore;
            }

            $metadata = $lockedStore->metadata ?? [];
            Arr::set($metadata, 'offboarding', [
                'reason' => $reason,
                'offboarded_at' => now()->toIso8601String(),
                'previous_active' => $lockedStore->active,
            ]);

            $lockedStore->forceFill([
                'active' => false,
                'metadata' => $metadata,
            ])->save();
            $lockedStore->delete();

            return $lockedStore;
        });
    }
}
