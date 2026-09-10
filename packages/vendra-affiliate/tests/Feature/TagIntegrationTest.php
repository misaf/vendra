<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Tests\Feature;

use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraSupport\Capabilities\EloquentTagResolver;
use Misaf\VendraSupport\Contracts\TagResolver;
use Misaf\VendraSupport\Support\TagRelationship;

it('builds an affiliate typed tag relation through the support contract', function (): void {
    app()->instance(TagResolver::class, new EloquentTagResolver(new TagRelationship(AffiliateTestTag::class)));

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
