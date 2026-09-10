<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Unit;

use Misaf\VendraSupport\Capabilities\AttributeIntegration;
use Misaf\VendraSupport\Capabilities\NullAttributeResolver;
use Misaf\VendraSupport\Contracts\AttributeResolver;

it('falls back to unavailable attribute integration', function (): void {
    app()->instance(AttributeResolver::class, new class implements AttributeResolver
    {
        public function available(): bool
        {
            return false;
        }

        public function valueModel(): ?string
        {
            return null;
        }

        public function options(): array
        {
            return [];
        }
    });

    expect(AttributeIntegration::isAvailable())->toBeFalse()
        ->and(AttributeIntegration::valueModel())->toBeNull()
        ->and(AttributeIntegration::options())->toBe([]);
});

it('uses the bound attribute resolver when available', function (): void {
    app()->instance(AttributeResolver::class, new class implements AttributeResolver
    {
        public function available(): bool
        {
            return true;
        }

        public function valueModel(): ?string
        {
            return SupportTestAttributeValue::class;
        }

        public function options(): array
        {
            return [1 => 'Weight (kg)'];
        }
    });

    expect(AttributeIntegration::isAvailable())->toBeTrue()
        ->and(AttributeIntegration::valueModel())->toBe(SupportTestAttributeValue::class)
        ->and(AttributeIntegration::options())->toBe([1 => 'Weight (kg)']);
});

it('falls back when the bound attribute resolver throws', function (): void {
    app()->instance(AttributeResolver::class, new class implements AttributeResolver
    {
        public function available(): bool
        {
            throw new RuntimeException('Resolver failed.');
        }

        public function valueModel(): ?string
        {
            throw new RuntimeException('Resolver failed.');
        }

        public function options(): array
        {
            throw new RuntimeException('Resolver failed.');
        }
    });

    expect(AttributeIntegration::isAvailable())->toBeFalse()
        ->and(AttributeIntegration::valueModel())->toBeNull()
        ->and(AttributeIntegration::options())->toBe([]);
});

it('uses the support null resolver fallback shape', function (): void {
    $resolver = new NullAttributeResolver;

    expect($resolver->available())->toBeFalse()
        ->and($resolver->valueModel())->toBeNull()
        ->and($resolver->options())->toBe([]);
});
