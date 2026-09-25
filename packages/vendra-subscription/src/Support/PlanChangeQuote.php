<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;

/**
 * What switching from the current subscription to a plan would do.
 *
 * An upgrade (a higher price per second, or the same price with more units)
 * applies now; anything else waits for the end of the current period. A paid,
 * running period keeps its billing anchor on upgrade and collects only the
 * price difference for the time left.
 *
 * Prices in different currencies cannot be compared or netted, so a change
 * from a paid period to a paid plan in another currency waits too.
 */
final readonly class PlanChangeQuote
{
    private function __construct(
        public bool $appliesNow,
        public ?Carbon $endsAt,
        public ?int $amount,
    ) {}

    public static function for(?Subscription $current, Plan $plan): self
    {
        $now = Date::now();

        if ($current === null || ! $current->isActive() || $current->ends_at === null) {
            return new self(appliesNow: true, endsAt: null, amount: null);
        }

        if (self::changesPaidCurrency($current, $plan)) {
            return new self(appliesNow: false, endsAt: null, amount: null);
        }

        $currentSeconds = self::seconds($current->starts_at, $current->ends_at);
        $planSeconds = self::seconds($now, $plan->resolveEndDate($now));

        if (! self::isUpgrade($current, $plan, $currentSeconds, $planSeconds)) {
            return new self(appliesNow: false, endsAt: null, amount: null);
        }

        if ($current->price === 0 || $current->isOnTrial() || $plan->isFree()) {
            return new self(appliesNow: true, endsAt: null, amount: null);
        }

        $remainingSeconds = self::seconds($now, $current->ends_at);
        $difference = $plan->price * $remainingSeconds / $planSeconds
            - $current->price * $remainingSeconds / $currentSeconds;

        return new self(
            appliesNow: true,
            endsAt: $current->ends_at->copy(),
            amount: max(1, (int) ceil(round($difference, 6))),
        );
    }

    /**
     * Whether the change keeps the current billing anchor and collects a prorated amount.
     *
     * @phpstan-assert-if-true !null $this->endsAt
     * @phpstan-assert-if-true !null $this->amount
     */
    public function isProrated(): bool
    {
        return $this->endsAt !== null && $this->amount !== null;
    }

    private static function changesPaidCurrency(Subscription $current, Plan $plan): bool
    {
        return $current->price > 0
            && ! $current->isOnTrial()
            && ! $plan->isFree()
            && $current->currency_code !== $plan->currency_code;
    }

    private static function isUpgrade(Subscription $current, Plan $plan, int $currentSeconds, int $planSeconds): bool
    {
        $planRate = $plan->price * $currentSeconds;
        $currentRate = $current->price * $planSeconds;

        if ($planRate !== $currentRate) {
            return $planRate > $currentRate;
        }

        return $plan->max_units > ($current->plan->max_units ?? 0);
    }

    private static function seconds(Carbon $from, Carbon $until): int
    {
        return max(1, (int) $from->diffInSeconds($until, absolute: true));
    }
}
