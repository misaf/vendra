<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Tests\Feature;

use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraSupport\Capabilities\EloquentTagResolver;
use Misaf\VendraSupport\Contracts\TagResolver;
use Misaf\VendraSupport\Support\TagRelationship;

it('builds an attribute typed tag relation through the support contract', function (): void {
    app()->instance(TagResolver::class, new EloquentTagResolver(new TagRelationship(AttributeTestTag::class)));

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
