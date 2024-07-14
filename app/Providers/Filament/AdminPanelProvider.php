<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Facades\Filament;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\SpatieLaravelTranslatablePlugin;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession;
use Spatie\Multitenancy\Http\Middleware\NeedsTenant;
use Termehsoft\Language\Models\Language;

final class AdminPanelProvider extends PanelProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // $this->registerFont();
        // $this->registerPlugins();
        $this->registerComponents();
        $this->registerTableActions();
        // $this->registerSpa();

        // Set Global Configuration
        // SetGlobalConfigurationTask::makeConfiguration(filament()->getTenant());
    }

    /**
     * Define the Filament admin panel configuration.
     *
     * @param Panel $panel
     * @return Panel
     */
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('ecommerce')
            ->login()
            ->default()
            ->brandName('commerce')
            ->path('/admin')
            ->profile(isSimple: false)
            ->brandLogoHeight('2rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->middleware([
                NeedsTenant::class,
                StartSession::class,
                EnsureValidTenantSession::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                SpatieLaravelTranslatablePlugin::make()->defaultLocales($this->getLocales()),
            ])
            ->font('yekan', 'https://cdn.font-store.ir/yekan.css', LocalFontProvider::class)
            ->discoverClusters(app_path('Filament/Admin/Clusters'), 'App\\Filament\\Admin\\Clusters')
            ->discoverPages(app_path('Filament/Admin/Pages'), 'App\\Filament\\Admin\\Pages')
            ->discoverResources(app_path('Filament/Admin/Resources'), 'App\\Filament\\Admin\\Resources')
            ->discoverWidgets(app_path('Filament/Admin/Widgets'), 'App\\Filament\\Admin\\Widgets')
            ->databaseNotifications()
            ->databaseTransactions()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->navigationGroups($this->getNavigationGroups())
            ->viteTheme('resources/css/filament/admin/theme.css');
        ;
    }

    public function registerSpa(): void
    {
        filament()->getCurrentPanel()->spa(true);
    }

    /**
     * Get the locales.
     *
     * @return array
     */
    private function getLocales(): array
    {
        // return Language::where('status', true)->pluck('iso_code')->toArray();
        return ['fa'];
    }

    /**
     * Get the menu items.
     *
     * @return array
     */
    private function getMenuItems(): array
    {
        return ['register' => MenuItem::make()->label('Register new team')];
    }

    /**
     * Get the navigation groups.
     *
     * @return array
     */
    private function getNavigationGroups(): array
    {
        return [
            NavigationGroup::make()->label(fn(): string => __('navigation.product_management'))->icon('heroicon-o-building-storefront')->collapsed(),
            NavigationGroup::make()->label(fn(): string => __('navigation.blog_management'))->icon('heroicon-o-presentation-chart-line')->collapsed(),
            NavigationGroup::make()->label(fn(): string => __('navigation.currency_management'))->icon('heroicon-o-currency-dollar')->collapsed(),
            NavigationGroup::make()->label(fn(): string => __('navigation.faq_management'))->icon('heroicon-o-question-mark-circle')->collapsed(),
            NavigationGroup::make()->label(fn(): string => __('navigation.user_management'))->icon('heroicon-o-users')->collapsed(),
            NavigationGroup::make()->label(fn(): string => __('navigation.geographical_management'))->icon('heroicon-o-globe-europe-africa')->collapsed(),
            NavigationGroup::make()->label(fn(): string => __('navigation.report_management'))->icon('heroicon-o-bug-ant')->collapsed(),
            NavigationGroup::make()->label(fn(): string => __('navigation.setting_management'))->icon('heroicon-o-cog-6-tooth')->collapsed(),
        ];
    }

    /**
     * Register the components.
     */
    private function registerComponents(): void
    {
        LanguageSwitch::configureUsing(fn(LanguageSwitch $switch): LanguageSwitch => $switch->locales($this->getLocales()));
    }

    /**
     * Register the font.
     */
    private function registerFont(): void
    {
        filament()->getCurrentPanel()->font('yekan', 'https://cdn.font-store.ir/yekan.css', LocalFontProvider::class);
    }

    /**
     * Register the plugins.
     */
    private function registerPlugins(): void
    {
        filament()->getCurrentPanel()->plugins([
            SpatieLaravelTranslatablePlugin::make()->defaultLocales($this->getLocales()),
        ]);
    }

    /**
     * Register table actions.
     */
    private function registerTableActions(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table->paginationPageOptions([10, 25, 50]);
        });

        ViewAction::configureUsing(function (ViewAction $viewAction): void {
            $viewAction->button();
        });

        EditAction::configureUsing(function (EditAction $editAction): void {
            $editAction->button();
        });

        DeleteAction::configureUsing(function (DeleteAction $deleteAction): void {
            $deleteAction->button();
        });
    }

    /**
     * Get the tenant Domains.
     *
     * @return array
     */
    private function tenantDomains(): array
    {
        return [];
    }
}
