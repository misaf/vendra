<?php

declare(strict_types=1);

namespace App\Http\Middleware\Filament;

use App\Models\Language\Language;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Closure;
use Filament\Facades\Filament;
use Filament\FontProviders\LocalFontProvider;
use Filament\SpatieLaravelTranslatablePlugin;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

final class ConfigMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->configureFont();
        $this->configureVite();
        $this->registerAssets();
        $this->configureLanguageSwitch();
        $this->registerTranslatablePlugin();

        return $next($request);
    }

    /**
     * Configure the font.
     */
    private function configureFont(): void
    {
        Filament::getCurrentPanel()
            ->font('yekan', 'https://cdn.font-store.ir/yekan.css', LocalFontProvider::class);
    }

    /**
     * Configure the language switch.
     */
    private function configureLanguageSwitch(): void
    {
        LanguageSwitch::configureUsing(fn(LanguageSwitch $switch) => $switch->locales($this->getLocales()));
    }

    /**
     * Configure Vite for asset management.
     */
    private function configureVite(): void
    {
        Vite::createAssetPathsUsing(fn(string $path, ?bool $secure) => Config::get('app.asset_url') . "/{$path}");
    }

    /**
     * Get the locales.
     *
     * @return array
     */
    private function getLocales(): array
    {
        return Language::where('status', true)->pluck('iso_code')->toArray();
    }

    /**
     * Register custom assets.
     */
    private function registerAssets(): void
    {
        FilamentAsset::register([Css::make('font-yekan', '//cdn.font-store.ir/yekan.css')]);
    }

    /**
     * Register the translatable plugin.
     */
    private function registerTranslatablePlugin(): void
    {
        Filament::getCurrentPanel()
            ->plugin(SpatieLaravelTranslatablePlugin::make()->defaultLocales($this->getLocales()));
    }
}
