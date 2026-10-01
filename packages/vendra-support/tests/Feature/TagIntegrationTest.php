<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Feature;

use Misaf\VendraSupport\Capabilities\NullTagResolver;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Contracts\TagResolver;
use Misaf\VendraSupport\Support\TagRelationship;
use Misaf\VendraSupport\Tests\Unit\SupportTestTag;
use RuntimeException;

it('falls back to unavailable tag integration', function (): void {
    app()->instance(TagResolver::class, new NullTagResolver);

    expect(TagIntegration::isAvailable())->toBeFalse()
        ->and(TagIntegration::relationship())->toBeNull();
});

it('exposes relationship metadata from the bound tag resolver', function (): void {
    $relationship = new TagRelationship(SupportTestTag::class);

    $resolver = $this->mock(TagResolver::class);
    $resolver->shouldReceive('available')->andReturnTrue();
    $resolver->shouldReceive('relationship')->andReturn($relationship);

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

it('falls back when no tag resolver is registered', function (): void {
    app()->offsetUnset(TagResolver::class);

    expect(TagIntegration::isAvailable())->toBeFalse()
        ->and(TagIntegration::relationship())->toBeNull();
});
