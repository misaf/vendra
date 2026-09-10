<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Misaf\VendraBlog\Filament\Clusters\Resources\BlogPostCategories\Pages\CreateBlogPostCategory;
use Misaf\VendraBlog\Filament\Clusters\Resources\BlogPostCategories\Pages\EditBlogPostCategory;
use Misaf\VendraBlog\Models\BlogPostCategory;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();

    $this->activeLocale = app()->getLocale();
    $this->otherLocale = $this->activeLocale === 'en' ? 'de' : 'en';

    Filament::getPanel('admin')->plugin(
        SpatieTranslatablePlugin::make()->defaultLocales([$this->activeLocale, $this->otherLocale]),
    );
});

it('rejects a duplicate name and slug in the active locale', function (): void {
    BlogPostCategory::factory()->create([
        'name' => [$this->activeLocale => 'News'],
        'slug' => [$this->activeLocale => 'news'],
    ]);

    livewire(CreateBlogPostCategory::class)
        ->fillForm([
            'name' => 'News',
            'slug' => 'news',
        ])
        ->call('create')
        ->assertHasFormErrors(['name', 'slug']);
});

it('allows a name and slug that only exist in another locale', function (): void {
    BlogPostCategory::factory()->create([
        'name' => [$this->otherLocale => 'News'],
        'slug' => [$this->otherLocale => 'news'],
    ]);

    livewire(CreateBlogPostCategory::class)
        ->fillForm([
            'name' => 'News',
            'slug' => 'news',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(BlogPostCategory::query()->count())->toBe(2);
});

it('saves an edited category without changing its unique fields', function (): void {
    $blogPostCategory = BlogPostCategory::factory()->create([
        'name' => [$this->activeLocale => 'News'],
        'slug' => [$this->activeLocale => 'news'],
    ]);

    livewire(EditBlogPostCategory::class, ['record' => $blogPostCategory->getKey()])
        ->call('save')
        ->assertHasNoFormErrors();
});
