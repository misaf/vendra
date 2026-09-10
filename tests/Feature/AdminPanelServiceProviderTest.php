<?php

declare(strict_types=1);

use App\Providers\Filament\AdminPanelServiceProvider;
use Filament\Panel;
use Filament\Support\Enums\Width;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Misaf\VendraConsole\Providers\ConsolePanelServiceProvider;
use Misaf\VendraReseller\Models\ResellerUser;
use Misaf\VendraReseller\Providers\ResellerPanelServiceProvider;
use Symfony\Component\HttpFoundation\Cookie;

it('uses a compact sidebar width', function (): void {
    $panel = (new AdminPanelServiceProvider(app()))->panel(Panel::make());

    expect($panel->getSidebarWidth())->toBe('14rem');
});

it('uses dedicated domains and root paths for central panels', function (): void {
    config()->set('app.url', 'https://vendra.test');

    $resellerPanel = (new ResellerPanelServiceProvider(app()))->panel(Panel::make());
    $consolePanel = (new ConsolePanelServiceProvider(app()))->panel(Panel::make());

    expect($resellerPanel->getDomains())->toBe(['reseller.vendra.test'])
        ->and($resellerPanel->getPath())->toBe('')
        ->and($resellerPanel->getAuthGuard())->toBe('reseller')
        ->and($resellerPanel->getAuthPasswordBroker())->toBe('reseller_users')
        ->and($consolePanel->getDomains())->toBe(['console.vendra.test'])
        ->and($consolePanel->getPath())->toBe('')
        ->and(config('session.domain'))->toBeNull();
});

it('issues host-only session cookies for central panels', function (string $url): void {
    $sessionCookie = collect($this->get($url)->headers->getCookies())
        ->first(fn (Cookie $cookie): bool => $cookie->getName() === config('session.cookie'));

    expect($sessionCookie)->not->toBeNull()
        ->and($sessionCookie?->getDomain())->toBeNull();
})->with([
    'console' => 'https://console.vendra.test/login',
    'reseller' => 'https://reseller.vendra.test/login',
]);

it('generates central panel assets from the current panel domain', function (string $url, string $origin): void {
    $this->get($url)
        ->assertSuccessful()
        ->assertSee($origin.'/images/vendra-logo.svg', escape: false)
        ->assertSee('src="'.$origin.'/livewire-', escape: false)
        ->assertSee('data-update-uri="'.$origin.'/livewire-', escape: false);

    expect(config('app.url'))->toBe('https://vendra.test')
        ->and(config('app.asset_url'))->toBe('https://vendra.test')
        ->and(asset('images/vendra-logo.svg'))->toBe('https://vendra.test/images/vendra-logo.svg');
})->with([
    'console' => ['https://console.vendra.test/login', 'https://console.vendra.test'],
    'reseller' => ['https://reseller.vendra.test/login', 'https://reseller.vendra.test'],
]);

it('keeps proxied central panel assets on https via X-Forwarded-Proto', function (string $origin): void {
    // Simulate Traefik: the request reaches the app as plain HTTP but carries
    // X-Forwarded-Proto: https. Trusted proxies must make asset URLs https,
    // otherwise the https page blocks them as mixed content.
    $httpUrl = str_replace('https://', 'http://', $origin).'/login';

    $this->get($httpUrl, ['X-Forwarded-Proto' => 'https'])
        ->assertSuccessful()
        ->assertSee($origin.'/images/vendra-logo.svg', escape: false)
        ->assertSee('src="'.$origin.'/livewire-', escape: false)
        ->assertDontSee(str_replace('https://', 'http://', $origin).'/livewire-', escape: false);
})->with([
    'console' => 'https://console.vendra.test',
    'reseller' => 'https://reseller.vendra.test',
]);

it('isolates reseller authentication configuration', function (): void {
    expect(config('auth.guards.reseller.provider'))->toBe('reseller_users')
        ->and(config('auth.providers.reseller_users.model'))->toBe(ResellerUser::class)
        ->and(config('auth.passwords.reseller_users.table'))->toBe('reseller_password_reset_tokens');
});

it('uses the full content width for localized navigation and pages', function (): void {
    $panel = (new AdminPanelServiceProvider(app()))->panel(Panel::make());

    expect($panel->getMaxContentWidth())->toBe(Width::Full);
});

it('uses the Vendra logo in light and dark modes', function (): void {
    $panel = (new AdminPanelServiceProvider(app()))->panel(Panel::make());

    expect($panel->getBrandName())->toBe('Vendra')
        ->and($panel->getBrandLogo())->toBe(asset('images/vendra-logo.svg'))
        ->and($panel->getDarkModeBrandLogo())->toBe(asset('images/vendra-logo-dark.svg'))
        ->and($panel->getBrandLogoHeight())->toBe('2rem')
        ->and(public_path('images/vendra-logo.svg'))->toBeFile()
        ->and(public_path('images/vendra-logo-dark.svg'))->toBeFile();
});

it('uses the font matching the application locale', function (string $locale, string $font): void {
    app()->setLocale($locale);

    $panel = (new AdminPanelServiceProvider(app()))->panel(Panel::make());

    expect($panel->getFontFamily())->toBe($font);
})->with([
    'Persian' => ['fa', 'Vazirmatn'],
    'English' => ['en', 'Google'],
    'German' => ['de', 'Google'],
]);

it('uses the Vendra Language catalog for translatable resources', function (): void {
    config()->set('vendra-language.locales', ['EN', 'pt_br']);

    $panel = (new AdminPanelServiceProvider(app()))->panel(Panel::make());
    $plugin = $panel->getPlugin('spatie-translatable');

    expect($plugin)->toBeInstanceOf(SpatieTranslatablePlugin::class)
        ->and($plugin->getDefaultLocales())->toBe(['en', 'pt-BR']);
});
