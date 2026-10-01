<?php

declare(strict_types=1);

namespace Misaf\VendraBlog\Tests\Feature;

use LogicException;
use Misaf\VendraBlog\Models\BlogPost;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Contracts\TagResolver;
use Misaf\VendraSupport\Support\TagRelationship;

it('builds a blog typed tag relation through the support contract', function (): void {
    $resolver = $this->mock(TagResolver::class);
    $resolver->shouldReceive('available')->andReturnTrue();
    $resolver->shouldReceive('relationship')->andReturn(new TagRelationship(BlogTestTag::class));

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

it('keeps blog tags unavailable when no tag resolver is registered', function (): void {
    app()->offsetUnset(TagResolver::class);

    expect(TagIntegration::isAvailable())->toBeFalse()
        ->and(fn () => (new BlogPost)->tags())
        ->toThrow(LogicException::class, 'Install a tag provider to use tags.');
});
