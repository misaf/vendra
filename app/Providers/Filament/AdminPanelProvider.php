<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\Tenancy\EditTenantProfile;
use App\Filament\Admin\Pages\Tenancy\RegisterTenant;
use App\Http\Middleware\ApplyTenantScopes;
use App\Http\Middleware\Filament\ConfigMiddleware;
use App\Models\Tenant\Tenant;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

final class AdminPanelProvider extends PanelProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureTableActions();
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
            ->default()
            ->id('admin')
            ->path(app('request')->segment(1) . '/admin')
            ->login()
            ->profile(isSimple: false)
            ->brandLogoHeight('2rem')
            ->colors(['primary' => Color::Amber])
            ->authMiddleware($this->getAuthMiddleware())
            ->middleware($this->getMiddleware(), true)
            ->discoverClusters(app_path('Filament/Admin/Clusters'), 'App\\Filament\\Admin\\Clusters')
            ->discoverPages(app_path('Filament/Admin/Pages'), 'App\\Filament\\Admin\\Pages')
            ->discoverResources(app_path('Filament/Admin/Resources'), 'App\\Filament\\Admin\\Resources')
            ->discoverWidgets(app_path('Filament/Admin/Widgets'), 'App\\Filament\\Admin\\Widgets')
            ->pages($this->getPages())
            ->widgets($this->getWidgets())
            // ->tenant(Tenant::class, 'domain')
            // ->tenantDomain('{tenant:domain}')
            // ->tenantMenuItems($this->getMenuItems())
            // ->tenantProfile(EditTenantProfile::class)
            // ->tenantRegistration(RegisterTenant::class)
            ->databaseNotifications()
            ->databaseTransactions()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->navigationGroups($this->getNavigationGroups())
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->spa()
        ;
    }

    /**
     * Configure table actions.
     */
    private function configureTableActions(): void
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
     * Get the list of authentication middleware.
     *
     * @return array
     */
    private function getAuthMiddleware(): array
    {
        return [
            Authenticate::class,
        ];
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
     * Get the list of middleware.
     *
     * @return array
     */
    private function getMiddleware(): array
    {
        return [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
            ConfigMiddleware::class,
            // ApplyTenantScopes::class
        ];
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
     * Get the pages.
     *
     * @return array
     */
    private function getPages(): array
    {
        return [Dashboard::class];
    }

    /**
     * Get the widgets.
     *
     * @return array
     */
    private function getWidgets(): array
    {
        return [AccountWidget::class];
    }
}
