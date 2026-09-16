<?php

declare(strict_types=1);

namespace Misaf\VendraTagger\Tests\Feature;

use Misaf\VendraTagger\Filament\Forms\Components\ModelTagsInput;
use Misaf\VendraTagger\Filament\Infolists\Components\ModelTagsEntry;
use Misaf\VendraTagger\Filament\Tables\Columns\ModelTagsColumn;

it('defaults the tag field, entry and column to the tags relationship with the shared label', function (): void {
    $label = __('vendra-tagger::attributes.tags');

    expect(ModelTagsInput::make()->getName())->toBe('tags')
        ->and(ModelTagsInput::make()->getLabel())->toBe($label)
        ->and(ModelTagsInput::make()->isLive())->toBeTrue()
        ->and(ModelTagsEntry::make()->getName())->toBe('tags')
        ->and(ModelTagsEntry::make()->getLabel())->toBe($label)
        ->and(ModelTagsColumn::make()->getName())->toBe('tags')
        ->and(ModelTagsColumn::make()->getLabel())->toBe($label)
        ->and(ModelTagsColumn::make()->isToggleable())->toBeTrue()
        ->and(ModelTagsColumn::make()->type('faq')->getType())->toBe('faq');
});
