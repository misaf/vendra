<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Tests\Feature;

use LogicException;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Contracts\TagResolver;
use Misaf\VendraSupport\Support\TagRelationship;

it('builds an affiliate typed tag relation through the support contract', function (): void {
    $resolver = $this->mock(TagResolver::class);
    $resolver->shouldReceive('available')->andReturnTrue();
    $resolver->shouldReceive('relationship')->andReturn(new TagRelationship(AffiliateTestTag::class));

    $relation = (new Affiliate)->tags();

    expect($relation->getRelated())->toBeInstanceOf(AffiliateTestTag::class)
        ->and($relation->getTable())->toBe('taggables')
        ->and($relation->toBase()->wheres)->toContainEqual([
            'type' => 'Basic',
            'column' => 'tags.type',
            'operator' => '=',
            'value' => Affiliate::TAG_TYPE,
            'boolean' => 'and',
        ]);
});

it('keeps affiliate tags unavailable when no tag resolver is registered', function (): void {
    app()->offsetUnset(TagResolver::class);

    expect(TagIntegration::isAvailable())->toBeFalse()
        ->and(fn () => (new Affiliate)->tags())
        ->toThrow(LogicException::class, 'Install a tag provider to use tags.');
});
