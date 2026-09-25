<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Misaf\VendraSubscription\Models\Subscription;

/**
 * Implementations are Eloquent models registered in the morph map; type against
 * `Model&SubscriptionSubscriber` when model behavior is needed.
 */
interface SubscriptionSubscriber
{
    public function activeSubscription(): ?Subscription;

    public function latestSubscription(): ?Subscription;

    public function hasSubscriptions(): bool;

    public function canHoldUnits(): bool;

    public function notifyContact(Notification $notification): void;

    public function subscriptionPayer(): ?Model;

    public function subscribedUnitCount(): int;

    public function activeSubscribedUnitCount(): int;

    /**
     * The buyer named on invoices.
     *
     * @return array{name: string, email: string|null, address: string|null, tax_id: string|null}
     */
    public function billingDetails(): array;
}
