<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraSupport\Filament\Infolists\Components\DescriptionEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\IsDefaultEntry;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\IsDefaultConstraint;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Schema::create('support_test_sluggable_records', function (Blueprint $table): void {
        $table->id();
        $table->json('name');
        $table->json('slug');
        $table->softDeletes();
        $table->timestamps();
    });
});

it('derives the slug from the name until the slug is edited by hand', function (): void {
    livewire(SupportTestSluggableFormComponent::class)
        ->set('data.name', 'Summer Sale')
        ->assertSet('data.slug', 'summer-sale')
        ->set('data.name', 'Winter Sale')
        ->assertSet('data.slug', 'winter-sale')
        ->set('data.slug', 'custom')
        ->set('data.name', 'Spring Sale')
        ->assertSet('data.slug', 'custom');
});

it('keeps the slug unique per active locale while ignoring trashed records', function (): void {
    SupportTestSluggableRecord::query()->create(['name' => ['en' => 'Taken'], 'slug' => ['en' => 'taken']]);
    SupportTestSluggableRecord::query()->create(['name' => ['en' => 'Gone'], 'slug' => ['en' => 'gone']])->delete();

    livewire(SupportTestSluggableFormComponent::class)
        ->set('data.name', 'Taken')
        ->call('save')
        ->assertHasErrors(['data.slug' => 'unique']);

    livewire(SupportTestSluggableFormComponent::class)
        ->set('activeLocale', 'de')
        ->set('data.name', 'Taken')
        ->call('save')
        ->assertHasNoErrors(['data.slug']);

    livewire(SupportTestSluggableFormComponent::class)
        ->set('data.name', 'Gone')
        ->call('save')
        ->assertHasNoErrors(['data.slug']);
});

it('validates the description as soon as it changes', function (): void {
    livewire(SupportTestSluggableFormComponent::class)
        ->set('data.description', 'far too long for the limit')
        ->assertHasErrors(['data.description' => 'max']);
});

it('renders rich description content as html', function (): void {
    $document = ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Hello']]]]];

    expect(DescriptionEntry::make()->getName())->toBe('description')
        ->and(DescriptionEntry::make()->getLabel())->toBe(__('vendra-support::attributes.description'))
        ->and((string) DescriptionEntry::make()->richContent()->formatState($document))->toBe('<p>Hello</p>');
});

it('starts the default toggle off and validates it as soon as it changes', function (): void {
    livewire(SupportTestSluggableFormComponent::class)
        ->assertSet('data.is_default', false)
        ->set('data.is_default', true)
        ->assertHasNoErrors()
        ->set('data.is_default', null)
        ->assertHasErrors(['data.is_default' => 'required']);
});

it('labels the default entry and constraint with the shared translation', function (): void {
    $entry = IsDefaultEntry::make();
    $constraint = IsDefaultConstraint::make();

    expect($entry->getName())->toBe('is_default')
        ->and($entry->getLabel())->toBe(__('vendra-support::attributes.is_default'))
        ->and($entry->isBoolean())->toBeTrue()
        ->and($constraint->getName())->toBe('is_default')
        ->and($constraint->getLabel())->toBe(__('vendra-support::attributes.is_default'));
});
