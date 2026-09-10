<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Unit;

use Misaf\VendraSupport\Capabilities\EloquentTagResolver;
use Misaf\VendraSupport\Capabilities\NullTagResolver;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Contracts\TagResolver;
use Misaf\VendraSupport\Support\TagRelationship;
use RuntimeException;

it('falls back to unavailable tag integration', function (): void {
    app()->instance(TagResolver::class, new NullTagResolver);

    expect(TagIntegration::isAvailable())->toBeFalse()
        ->and(TagIntegration::relationship())->toBeNull();
});

it('exposes relationship metadata from the bound tag resolver', function (): void {
    $relationship = new TagRelationship(SupportTestTag::class);

    app()->instance(TagResolver::class, new EloquentTagResolver($relationship));

    expect(TagIntegration::isAvailable())->toBeTrue()
        ->and(TagIntegration::relationship())->toBe($relationship);
});

it('falls back when the bound tag resolver throws', function (): void {
    app()->instance(TagResolver::class, new class implements TagResolver
    {
        public function available(): bool
        {
            throw new RuntimeException('Resolver failed.');
        }

        public function relationship(): ?TagRelationship
        {
            throw new RuntimeException('Resolver failed.');
        }
    });

    expect(TagIntegration::isAvailable())->toBeFalse()
        ->and(TagIntegration::relationship())->toBeNull();
});
