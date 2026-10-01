<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Tests\Feature;

use LogicException;
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Contracts\TagResolver;
use Misaf\VendraSupport\Support\TagRelationship;

it('builds an attribute typed tag relation through the support contract', function (): void {
    $resolver = $this->mock(TagResolver::class);
    $resolver->shouldReceive('available')->andReturnTrue();
    $resolver->shouldReceive('relationship')->andReturn(new TagRelationship(AttributeTestTag::class));

    $relation = (new Attribute)->tags();

    expect($relation->getRelated())->toBeInstanceOf(AttributeTestTag::class)
        ->and($relation->getTable())->toBe('taggables')
        ->and($relation->toBase()->wheres)->toContainEqual([
            'type' => 'Basic',
            'column' => 'tags.type',
            'operator' => '=',
            'value' => Attribute::TAG_TYPE,
            'boolean' => 'and',
        ]);
});

it('keeps attribute tags unavailable when no tag resolver is registered', function (): void {
    app()->offsetUnset(TagResolver::class);

    expect(TagIntegration::isAvailable())->toBeFalse()
        ->and(fn () => (new Attribute)->tags())
        ->toThrow(LogicException::class, 'Install a tag provider to use tags.');
});
