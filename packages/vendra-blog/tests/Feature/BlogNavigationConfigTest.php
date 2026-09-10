<?php

declare(strict_types=1);

use Misaf\VendraBlog\BlogPlugin;
use Misaf\VendraBlog\Providers\BlogServiceProvider;

it('defaults the blog module to the admin panel', function (): void {
    expect(config('vendra-blog.panels'))->toBe(['admin']);
});

it('delegates tenant awareness to the tenant resolver instead of module config', function (): void {
    expect(config('vendra-blog.tenant_aware'))->toBeNull();
});

it('resolves configured panel ids from an array, string, or legacy panel key', function (): void {
    $provider = new BlogServiceProvider(app());
    $method = new ReflectionMethod($provider, 'configuredPanelIds');

    config(['vendra-blog.panels' => ['admin', 'vendor']]);

    expect($method->invoke($provider, 'vendra-blog'))->toBe(['admin', 'vendor']);

    config(['vendra-blog.panels' => 'admin']);

    expect($method->invoke($provider, 'vendra-blog'))->toBe(['admin']);

    config([
        'vendra-blog.panels' => null,
        'vendra-blog.panel' => 'legacy',
    ]);

    expect($method->invoke($provider, 'vendra-blog'))->toBe(['legacy']);
});

it('resolves the module-owned navigation group by default', function (): void {
    expect(BlogPlugin::make()->getNavigationGroup())
        ->toBe(__('vendra-support::navigation.groups.Content'));
});

it('lets the navigation group be overridden with a plugin option', function (): void {
    expect(BlogPlugin::make()->navigationGroup('Content')->getNavigationGroup())
        ->toBe('Content')
        ->and(BlogPlugin::make()->navigationGroup(fn (): string => 'Grouped')->getNavigationGroup())
        ->toBe('Grouped');
});

it('falls back to the configured navigation group when no plugin option is set', function (): void {
    config(['vendra-blog.navigation_group' => 'vendra-blog::navigation.blog']);

    expect(BlogPlugin::make()->getNavigationGroup())
        ->toBe(__('vendra-blog::navigation.blog'));
});
