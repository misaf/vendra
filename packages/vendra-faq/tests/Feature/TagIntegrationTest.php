<?php

declare(strict_types=1);

namespace Misaf\VendraFaq\Tests\Feature;

use Misaf\VendraFaq\Models\Faq;
use Misaf\VendraSupport\Capabilities\EloquentTagResolver;
use Misaf\VendraSupport\Contracts\TagResolver;
use Misaf\VendraSupport\Support\TagRelationship;

it('builds an faq typed tag relation through the support contract', function (): void {
    app()->instance(TagResolver::class, new EloquentTagResolver(new TagRelationship(FaqTestTag::class)));

    $relation = (new Faq)->tags();

    expect($relation->getRelated())->toBeInstanceOf(FaqTestTag::class)
        ->and($relation->getTable())->toBe('taggables')
        ->and($relation->toBase()->wheres)->toContainEqual([
            'type' => 'Basic',
            'column' => 'tags.type',
            'operator' => '=',
            'value' => Faq::TAG_TYPE,
            'boolean' => 'and',
        ]);
});
