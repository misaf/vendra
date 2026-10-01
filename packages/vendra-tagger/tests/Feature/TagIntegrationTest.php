<?php

declare(strict_types=1);

namespace Misaf\VendraTagger\Tests\Feature;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Contracts\TagResolver;
use Misaf\VendraTagger\Models\Tagger;

it('provides tag relationship metadata through the support contract', function (): void {
    $relationship = TagIntegration::relationship();

    expect(TagIntegration::isAvailable())->toBeTrue()
        ->and($relationship)->not->toBeNull()
        ->and($relationship?->model)->toBe(Tagger::class)
        ->and($relationship?->morphName)->toBe('taggable')
        ->and($relationship?->table)->toBe('taggables')
        ->and($relationship?->foreignPivotKey)->toBe('taggable_id')
        ->and($relationship?->relatedPivotKey)->toBe('tag_id');
});

it('provides the configured tag relationship metadata', function (): void {
    config([
        'tags.taggable.morph_name' => 'labelable',
        'tags.taggable.table_name' => 'label_links',
        'tags.taggable.class_name' => MorphPivot::class,
    ]);
    app()->forgetInstance(TagResolver::class);

    $relationship = resolve(TagResolver::class)->relationship();

    expect($relationship?->model)->toBe(Tagger::class)
        ->and($relationship?->morphName)->toBe('labelable')
        ->and($relationship?->table)->toBe('label_links')
        ->and($relationship?->foreignPivotKey)->toBe('labelable_id')
        ->and($relationship?->relatedPivotKey)->toBe('tag_id')
        ->and($relationship?->pivotModel)->toBe(MorphPivot::class);
});
