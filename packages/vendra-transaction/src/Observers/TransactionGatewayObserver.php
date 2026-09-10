<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Observers;

use Misaf\VendraTransaction\Models\TransactionGateway;

final class TransactionGatewayObserver
{
    public function creating(TransactionGateway $gateway): void
    {
        if (! $gateway->active) {
            $gateway->is_default = false;

            return;
        }

        if (! TransactionGateway::query()->active()->exists()) {
            $gateway->is_default = true;
        }
    }

    public function saving(TransactionGateway $gateway): void
    {
        if (! $gateway->active) {
            $gateway->is_default = false;

            return;
        }

        if ($gateway->is_default) {
            TransactionGateway::query()
                ->where('is_default', true)
                ->whereKeyNot($gateway->getKey())
                ->update(['is_default' => false]);

            return;
        }

        if ($gateway->exists && $gateway->getOriginal('is_default') === true) {
            $hasAnotherDefault = TransactionGateway::query()
                ->active()
                ->where('is_default', true)
                ->whereKeyNot($gateway->getKey())
                ->exists();

            if (! $hasAnotherDefault) {
                $gateway->is_default = true;
            }
        }
    }

    public function saved(TransactionGateway $gateway): void
    {
        if ($gateway->wasChanged(['active', 'is_default'])) {
            $this->ensureActiveDefault();
        }
    }

    public function deleted(TransactionGateway $gateway): void
    {
        if (! $gateway->is_default) {
            return;
        }

        if ($gateway->exists) {
            $gateway->forceFill(['is_default' => false])->saveQuietly();
        }

        $this->ensureActiveDefault();
    }

    private function ensureActiveDefault(): void
    {
        if (TransactionGateway::query()->active()->where('is_default', true)->exists()) {
            return;
        }

        TransactionGateway::query()
            ->active()
            ->ordered()
            ->first()
            ?->update(['is_default' => true]);
    }
}
