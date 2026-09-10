<?php

declare(strict_types=1);

use Misaf\VendraAttribute\Models\AttributeValue;
use Misaf\VendraSupport\Contracts\AttributeResolver;

it('binds the shared attribute resolver contract', function (): void {
    $resolver = resolve(AttributeResolver::class);

    expect($resolver->available())->toBeTrue()
        ->and($resolver->valueModel())->toBe(AttributeValue::class);
});
