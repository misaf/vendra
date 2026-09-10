<?php

declare(strict_types=1);

use Misaf\VendraTagger\Providers\TaggerServiceProvider;
use Misaf\VendraTagger\TaggerPlugin;

it('defaults the tagger module to the admin panel', function (): void {
    expect(config('vendra-tagger.panels'))->toBe(['admin']);
});

it('delegates tenant awareness to the tenant resolver instead of module config', function (): void {
    expect(config('vendra-tagger.tenant_aware'))->toBeNull();
});

it('resolves configured panel ids from an array, string, or legacy panel key', function (): void {
    $provider = new TaggerServiceProvider(app());
    $method = new ReflectionMethod($provider, 'configuredPanelIds');

    config(['vendra-tagger.panels' => ['admin', 'vendor']]);

    expect($method->invoke($provider, 'vendra-tagger'))->toBe(['admin', 'vendor']);

    config(['vendra-tagger.panels' => 'admin']);

    expect($method->invoke($provider, 'vendra-tagger'))->toBe(['admin']);

    config([
        'vendra-tagger.panels' => null,
        'vendra-tagger.panel' => 'legacy',
    ]);

    expect($method->invoke($provider, 'vendra-tagger'))->toBe(['legacy']);
});

it('resolves and overrides the navigation group', function (): void {
    expect(TaggerPlugin::make()->getNavigationGroup())
        ->toBe(__('vendra-support::navigation.groups.Content'))
        ->and(TaggerPlugin::make()->navigationGroup('Content')->getNavigationGroup())
        ->toBe('Content')
        ->and(TaggerPlugin::make()->navigationGroup(fn (): string => 'Grouped')->getNavigationGroup())
        ->toBe('Grouped');
});

it('falls back to the configured navigation group', function (): void {
    config(['vendra-tagger.navigation_group' => 'vendra-tagger::navigation.tagger']);

    expect(TaggerPlugin::make()->getNavigationGroup())
        ->toBe(__('vendra-tagger::navigation.tagger'));
});
