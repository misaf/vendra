<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Misaf\VendraBlog\Enums\BlogPostCategoryPolicyEnum;
use Misaf\VendraBlog\Enums\BlogPostPolicyEnum;
use Misaf\VendraBlog\Filament\Clusters\Resources\BlogPosts\RelationManagers\BlogPostRelationManager;
use Misaf\VendraBlog\Models\BlogPost;
use Misaf\VendraBlog\Models\BlogPostCategory;
use Misaf\VendraBlog\Observers\BlogPostCategoryObserver;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraSupport\Tenancy\Scopes\TeamScope;
use Misaf\VendraSupport\Tenancy\Scopes\TenantScope;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraSupport\Tenancy\TenantSchema;

/**
 * Bind a tenant resolver reporting the given availability for the current test.
 */
function fakeTenantResolver(bool $available, ?int $currentId = null): TenantResolver
{
    $resolver = Mockery::mock(TenantResolver::class);
    $resolver->shouldReceive('available')->andReturn($available);
    $resolver->shouldReceive('currentId')->andReturn($currentId);
    $resolver->shouldReceive('foreignKey')->andReturn(TenantSchema::DEFAULT_FOREIGN_KEY);

    return $resolver;
}

it('defines the expected translatable blog models', function (): void {
    expect((new BlogPostCategory)->translatable)->toBe(['name', 'description', 'slug'])
        ->and((new BlogPost)->translatable)->toBe(['name', 'description', 'slug'])
        ->and((new BlogPostCategory)->getFillable())->toContain('name', 'description', 'slug', 'position', 'active')
        ->and((new BlogPost)->getFillable())->toContain('blog_post_category_id', 'name', 'description', 'slug', 'position', 'active')
        ->and((new BlogPostCategory)->getHidden())->toContain('tenant_id')
        ->and((new BlogPost)->getHidden())->toContain('tenant_id');
});

it('applies shared tenant ownership to blog models', function (): void {
    expect(class_uses_recursive(BlogPostCategory::class))->toContain(BelongsToTenant::class)
        ->and(class_uses_recursive(BlogPost::class))->toContain(BelongsToTenant::class);
});

it('always registers tenant scopes that self-disable without a current tenant', function (): void {
    BlogPost::clearBootedModels();
    app()->instance(TenantResolver::class, fakeTenantResolver(available: false));

    expect(array_keys((new BlogPost)->getGlobalScopes()))->toContain(TenantScope::class, TeamScope::class);

    BlogPost::clearBootedModels();
});

it('derives tenant awareness from the bound tenant resolver', function (): void {
    app()->instance(TenantResolver::class, fakeTenantResolver(available: true, currentId: 42));

    expect(TenantAwareness::enabled())->toBeTrue()
        ->and(TenantAwareness::currentId())->toBe(42);

    app()->instance(TenantResolver::class, fakeTenantResolver(available: false, currentId: 42));

    expect(TenantAwareness::enabled())->toBeFalse()
        ->and(TenantAwareness::currentId())->toBeNull();
});

it('is not tenant aware when the bound tenant resolver reports unavailable', function (): void {
    app()->instance(TenantResolver::class, fakeTenantResolver(available: false));

    expect(TenantAwareness::enabled())->toBeFalse()
        ->and(TenantAwareness::currentId())->toBeNull();
});

it('defines the expected blog relationships', function (): void {
    expect(new ReflectionMethod(BlogPostCategory::class, 'blogPosts')->getReturnType()?->getName())->toBe(HasMany::class)
        ->and(new ReflectionMethod(BlogPostCategory::class, 'multimedia')->getReturnType()?->getName())->toBe(MorphMany::class)
        ->and(new ReflectionMethod(BlogPost::class, 'blogPostCategory')->getReturnType()?->getName())->toBe(BelongsTo::class)
        ->and(new ReflectionMethod(BlogPost::class, 'multimedia')->getReturnType()?->getName())->toBe(MorphMany::class);
});

it('resolves blog post relation manager badges from loaded relations or counts', function (): void {
    $categoryWithLoadedPosts = new BlogPostCategory;
    $categoryWithLoadedPosts->setRelation('blogPosts', collect([
        new BlogPost,
        new BlogPost,
        new BlogPost,
    ]));

    $categoryWithCount = new BlogPostCategory;
    $categoryWithCount->setAttribute('blog_posts_count', '7');

    expect(BlogPostRelationManager::getBadge($categoryWithLoadedPosts, ''))->toBe('3')
        ->and(BlogPostRelationManager::getBadge($categoryWithCount, ''))->toBe('7')
        ->and(BlogPostRelationManager::isBadgeDeferred($categoryWithCount, ''))->toBeTrue();
});

it('registers the cascade observer on blog post categories', function (): void {
    $observerAttributes = new ReflectionClass(BlogPostCategory::class)->getAttributes(ObservedBy::class);

    expect($observerAttributes)->toHaveCount(1)
        ->and(Arr::get(Arr::get($observerAttributes, 0)->getArguments(), 0))->toBe([BlogPostCategoryObserver::class]);
});

it('defines policy permissions for all blog resources', function (): void {
    expect(array_column(BlogPostCategoryPolicyEnum::cases(), 'value'))->toBe([
        'create-blog-post-category',
        'delete-blog-post-category',
        'delete-any-blog-post-category',
        'force-delete-blog-post-category',
        'force-delete-any-blog-post-category',
        'reorder-blog-post-category',
        'restore-blog-post-category',
        'restore-any-blog-post-category',
        'update-blog-post-category',
        'view-blog-post-category',
        'view-any-blog-post-category',
    ])
        ->and(array_column(BlogPostPolicyEnum::cases(), 'value'))->toBe([
            'create-blog-post',
            'delete-blog-post',
            'delete-any-blog-post',
            'force-delete-blog-post',
            'force-delete-any-blog-post',
            'reorder-blog-post',
            'restore-blog-post',
            'restore-any-blog-post',
            'update-blog-post',
            'view-blog-post',
            'view-any-blog-post',
        ]);
});
