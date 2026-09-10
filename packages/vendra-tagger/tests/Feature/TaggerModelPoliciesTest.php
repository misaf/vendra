<?php

declare(strict_types=1);

use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraTagger\Database\Factories\TaggerFactory;
use Misaf\VendraTagger\Enums\TaggerPolicyEnum;
use Misaf\VendraTagger\Models\Tagger;

it('defines the expected tag model contract', function (): void {
    $tag = new Tagger;

    expect(class_uses_recursive(Tagger::class))->toContain(BelongsToTenant::class)
        ->and($tag->getTable())->toBe('tags')
        ->and($tag->getHidden())->toContain('tenant_id')
        ->and($tag->translatable)->toBe(['name', 'slug'])
        ->and($tag->getCasts())->toMatchArray([
            'tenant_id' => 'integer',
            'position' => 'integer',
        ])
        ->and($tag->determineOrderColumnName())->toBe('position')
        ->and($tag->shouldSortWhenCreating())->toBeTrue();
});

it('provides a package factory for tags', function (): void {
    $tag = TaggerFactory::new()->make();

    expect($tag)->toBeInstanceOf(Tagger::class)
        ->and($tag->getTranslation('name', app()->getLocale()))->not->toBeEmpty();
});

it('preserves a custom translated slug', function (): void {
    $tag = new Tagger([
        'name' => ['en' => 'Featured products'],
        'slug' => ['en' => 'Hand picked'],
    ]);
    $generateSlug = new ReflectionMethod($tag, 'generateSlug');

    expect($generateSlug->invoke($tag, 'en'))->toBe('hand-picked');
});

it('defines all permissions exposed by the tag resource', function (): void {
    expect(array_column(TaggerPolicyEnum::cases(), 'value'))->toBe([
        'create-tagger',
        'delete-tagger',
        'delete-any-tagger',
        'force-delete-tagger',
        'force-delete-any-tagger',
        'reorder-tagger',
        'restore-tagger',
        'restore-any-tagger',
        'update-tagger',
        'view-tagger',
        'view-any-tagger',
    ]);
});

it('uses unique kebab-case permission names', function (): void {
    $permissions = array_column(TaggerPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveSameSize(array_unique($permissions))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');
});
