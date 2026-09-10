<?php

declare(strict_types=1);

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\Facades\Config;
use Misaf\VendraSupport\Filament\Concerns\HasPluginNavigationGroup;
use Misaf\VendraSupport\Filament\Concerns\ResolvesPluginInstances;

function makeConcernTestPlugin(): Plugin
{
    return new class implements Plugin
    {
        use HasPluginNavigationGroup;
        use ResolvesPluginInstances;

        public function getId(): string
        {
            return 'concern-test-plugin';
        }

        protected function defaultNavigationGroup(): string
        {
            return 'vendra-support::navigation.groups.System';
        }

        public function register(Panel $panel): void {}

        public function boot(Panel $panel): void {}
    };
}

it('resolves fresh plugin instances from the container', function (): void {
    $plugin = makeConcernTestPlugin();

    expect($plugin::make())->toBeInstanceOf($plugin::class)
        ->and($plugin::make())->not->toBe($plugin);
});

it('falls back to the module default navigation group', function (): void {
    expect(makeConcernTestPlugin()->getNavigationGroup())
        ->toBe(__('vendra-support::navigation.groups.System'));
});

it('prefers the fluent navigation group override over config and defaults', function (): void {
    Config::set('concern-test-plugin.navigation_group', 'Configured');

    expect(makeConcernTestPlugin()->navigationGroup('Overridden')->getNavigationGroup())
        ->toBe('Overridden')
        ->and(makeConcernTestPlugin()->navigationGroup(fn (): string => 'Deferred')->getNavigationGroup())
        ->toBe('Deferred');
});

it('resolves the configured navigation group when no override is set', function (): void {
    Config::set('concern-test-plugin.navigation_group', 'vendra-support::navigation.groups.Sales');

    expect(makeConcernTestPlugin()->getNavigationGroup())
        ->toBe(__('vendra-support::navigation.groups.Sales'));
});

it('ignores blank or non-string configured navigation groups', function (): void {
    Config::set('concern-test-plugin.navigation_group', '');

    expect(makeConcernTestPlugin()->getNavigationGroup())
        ->toBe(__('vendra-support::navigation.groups.System'));
});
