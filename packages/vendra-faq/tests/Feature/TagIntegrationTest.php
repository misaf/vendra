<?php

declare(strict_types=1);

namespace Misaf\VendraFaq\Tests\Feature;

use LogicException;
use Misaf\VendraFaq\Models\Faq;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Contracts\TagResolver;
use Misaf\VendraSupport\Support\TagRelationship;

it('builds an faq typed tag relation through the support contract', function (): void {
    $resolver = $this->mock(TagResolver::class);
    $resolver->shouldReceive('available')->andReturnTrue();
    $resolver->shouldReceive('relationship')->andReturn(new TagRelationship(FaqTestTag::class));

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

it('keeps faq tags unavailable when no tag resolver is registered', function (): void {
    app()->offsetUnset(TagResolver::class);

    expect(TagIntegration::isAvailable())->toBeFalse()
        ->and(fn () => (new Faq)->tags())
        ->toThrow(LogicException::class, 'Install a tag provider to use tags.');
});
