<?php

declare(strict_types=1);

namespace Misaf\VendraBlog\Tests\Feature;

use Misaf\VendraBlog\Models\BlogPost;
use Misaf\VendraSupport\Capabilities\EloquentTagResolver;
use Misaf\VendraSupport\Contracts\TagResolver;
use Misaf\VendraSupport\Support\TagRelationship;

it('builds a blog typed tag relation through the support contract', function (): void {
    app()->instance(TagResolver::class, new EloquentTagResolver(new TagRelationship(BlogTestTag::class)));

    $relation = (new BlogPost)->tags();

    expect($relation->getRelated())->toBeInstanceOf(BlogTestTag::class)
        ->and($relation->getTable())->toBe('taggables')
        ->and($relation->toBase()->wheres)->toContainEqual([
            'type' => 'Basic',
            'column' => 'tags.type',
            'operator' => '=',
            'value' => BlogPost::TAG_TYPE,
            'boolean' => 'and',
        ]);
});
