<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Providers;

use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\VendraLanguage\Console\Commands\SeedCommand;
use Misaf\VendraLanguage\LanguagePlugin;
use Misaf\VendraLanguage\Localization\LanguageSwitchLocaleResolver;
use Misaf\VendraLanguage\Localization\NamespacedTranslationLoaderManager;
use Misaf\VendraLanguage\Localization\TenantLocaleResolver;
use Misaf\VendraLanguage\Localization\TranslationLoaders\DatabaseTranslationLoader;
use Misaf\VendraLanguage\Models\LanguageLine;
use Misaf\VendraLanguage\Support\Locales;
use Misaf\VendraLanguage\Support\TranslationLocales;
use Misaf\VendraLanguage\Support\TranslationProgress;
use Misaf\VendraLocalization\Contracts\LocaleResolver;
use Misaf\VendraLocalization\Resolvers\QueryLocaleResolver;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\TranslationLoader\LanguageLine as SpatieLanguageLine;
use Spatie\TranslationLoader\TranslationLoaderManager;
use Spatie\TranslationLoader\TranslationLoaders\Db;

final class LanguageServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-language')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations([
                'create_languages_table',
            ])
            ->hasConsoleCommand(SeedCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-language');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->scoped(TranslationProgress::class);

        $this->configureTranslationLoader();

        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), 'vendra-language')) {
                return;
            }

            $panel->plugin(LanguagePlugin::make());
        });
    }

    public function packageBooted(): void
    {
        /*
        | `languages` and `language_lines` are deliberately absent from the
        | TenantTableRegistry: a null tenant id is a platform row, so the
        | `vendra-tenant:enable` retrofit must never backfill those rows or
        | force the column NOT NULL.
        */
        $this->app->make(TenantSeeders::class)->register(SeedCommand::class, priority: 80);

        $this->configureLanguageSwitch();

        $this->configureLocalization();

        AboutCommand::add('Vendra Language', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-language')]);
    }

    /**
     * Use this module's namespace-aware translation loaders unless the host overrides them.
     */
    private function configureTranslationLoader(): void
    {
        $configuredTranslationLoaders = config('translation-loader.translation_loaders');

        /** @var array<int, class-string> $translationLoaders */
        $translationLoaders = is_array($configuredTranslationLoaders)
            ? $configuredTranslationLoaders
            : [DatabaseTranslationLoader::class];

        config([
            'translation-loader.translation_loaders' => array_map(
                static fn (string $translationLoader): string => $translationLoader === Db::class
                    ? DatabaseTranslationLoader::class
                    : $translationLoader,
                $translationLoaders,
            ),
        ]);

        if (in_array(config('translation-loader.model'), [null, SpatieLanguageLine::class], true)) {
            config(['translation-loader.model' => LanguageLine::class]);
        }

        if (in_array(config('translation-loader.translation_manager'), [null, TranslationLoaderManager::class], true)) {
            config(['translation-loader.translation_manager' => NamespacedTranslationLoaderManager::class]);
        }
    }

    /**
     * Supply the supported locales and the tenant locale resolver to localization.
     */
    private function configureLocalization(): void
    {
        if (! interface_exists(LocaleResolver::class) || ! config()->has('vendra-localization.resolvers')) {
            return;
        }

        config(['vendra-localization.supported_locales' => Locales::all()]);

        $resolvers = config()->array('vendra-localization.resolvers');

        if (! in_array(LanguageSwitchLocaleResolver::class, $resolvers, true)) {
            $queryResolverIndex = array_search(QueryLocaleResolver::class, $resolvers, true);
            $offset = is_int($queryResolverIndex) ? $queryResolverIndex + 1 : 0;

            array_splice($resolvers, $offset, 0, [LanguageSwitchLocaleResolver::class]);
        }

        if (! in_array(TenantLocaleResolver::class, $resolvers, true)) {
            $resolvers[] = TenantLocaleResolver::class;
        }

        config(['vendra-localization.resolvers' => $resolvers]);
    }

    private function configureLanguageSwitch(): void
    {
        LanguageSwitch::configureUsing(fn (LanguageSwitch $switch) => $switch
            ->locales(fn (): array => $this->availableLocales())
            ->visible());
    }

    /**
     * Get the tenant's active locales, or the fallback locale when there are none.
     *
     * @return string[]
     */
    private function availableLocales(): array
    {
        return TranslationLocales::active();
    }
}
