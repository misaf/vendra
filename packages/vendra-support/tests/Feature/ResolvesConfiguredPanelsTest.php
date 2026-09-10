<?php

declare(strict_types=1);

use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;

$resolver = new class
{
    use ResolvesConfiguredPanels;

    /**
     * @return array<int, string>
     */
    public function panelIds(string $configKey): array
    {
        return $this->configuredPanelIds($configKey);
    }

    public function registersOn(string $panelId, string $configKey): bool
    {
        return $this->shouldRegisterOnPanel($panelId, $configKey);
    }
};

it('defaults to the admin panel when nothing is configured', function () use ($resolver): void {
    expect($resolver->panelIds('vendra-example'))->toBe(['admin'])
        ->and($resolver->registersOn('admin', 'vendra-example'))->toBeTrue()
        ->and($resolver->registersOn('vendor', 'vendra-example'))->toBeFalse();
});

it('resolves panels from a string or array config value', function () use ($resolver): void {
    config(['vendra-example.panels' => 'vendor']);

    expect($resolver->panelIds('vendra-example'))->toBe(['vendor']);

    config(['vendra-example.panels' => ['admin', 'vendor', 42]]);

    expect($resolver->panelIds('vendra-example'))->toBe(['admin', 'vendor']);
});

it('falls back to the legacy panel key', function () use ($resolver): void {
    config([
        'vendra-example.panels' => null,
        'vendra-example.panel' => 'legacy',
    ]);

    expect($resolver->panelIds('vendra-example'))->toBe(['legacy'])
        ->and($resolver->registersOn('legacy', 'vendra-example'))->toBeTrue();
});
