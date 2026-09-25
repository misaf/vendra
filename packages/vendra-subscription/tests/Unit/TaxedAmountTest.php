<?php

declare(strict_types=1);

use Misaf\VendraSubscription\Support\TaxedAmount;

it('adds tax at a basis-point rate, rounding half up', function (int $net, int $rate, int $tax): void {
    $amount = TaxedAmount::for($net, $rate);

    expect($amount->tax)->toBe($tax)
        ->and($amount->total)->toBe($net + $tax);
})->with([
    'no tax' => [3_000, 0, 0],
    'whole' => [3_000, 1_900, 570],
    'rounds half up' => [50, 1_000, 5],
    'rounds down' => [44, 1_000, 4],
    'fractional rate' => [1_000, 750, 75],
]);
