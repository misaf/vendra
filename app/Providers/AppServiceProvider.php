<?php

declare(strict_types=1);

namespace App\Providers;

use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use BezhanSalleh\LanguageSwitch\Enums\Placement;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use BezhanSalleh\PanelSwitch\PanelSwitch;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Auth\Notifications\VerifyEmail;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Misaf\VendraMultilang\Facades\LanguageService;
use Misaf\VendraUser\Models\User;
use Throwable;

final class AppServiceProvider extends ServiceProvider
{
    /** @var list<string> */
    private array $panelRoles = ['super-admin', 'admin'];

    public function register(): void
    {
        $this->app->bind(Authenticatable::class, User::class);
        $this->app->bind(ResetPassword::class, ResetPasswordNotification::class);
        $this->app->bind(VerifyEmail::class, VerifyEmailNotification::class);
    }

    public function boot(): void
    {
        URL::forceScheme('https');
        Model::shouldBeStrict();
        DB::prohibitDestructiveCommands(app()->isProduction());
        Password::defaults(fn() => Password::min(8)->max(15));

        // Page::$reportValidationErrorUsing = function (Throwable $exception): void {
        //     Notification::make()
        //         ->title($exception->getMessage())
        //         ->danger()
        //         ->send();
        // };

        $this->configureTableDefaults();
        $this->configureLanguageSwitch();
        $this->configurePanelSwitch();
    }

    private function configureTableDefaults(): void
    {
        Table::configureUsing(function (Table $table) {
            return $table
                ->paginationPageOptions([10, 25, 50])
                ->deferLoading()
                ->defaultNumberLocale('en');
        });

        DatePicker::configureUsing(function (DatePicker $datePicker) {
            return $datePicker
                ->closeOnDateSelection()
                ->displayFormat('Y-m-d')
                ->firstDayOfWeek(6)
                ->unless(app()->isLocale('fa'), fn(DateTimePicker $column) => $column->jalali())
                ->native(false);
        });
    }

    private function configureLanguageSwitch(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            return $switch
                ->renderHook(PanelsRenderHook::GLOBAL_SEARCH_AFTER)
                ->locales($this->availableLocales())
                ->visible(outsidePanels: true)
                ->outsidePanelPlacement(Placement::TopCenter);
        });
    }

    private function configurePanelSwitch(): void
    {
        PanelSwitch::configureUsing(function (PanelSwitch $switch) {
            return $switch
                ->modalHeading(__('navigation.switch_panels'))
                ->modalWidth('sm')
                ->slideOver()
                ->icons([
                    'admin' => 'heroicon-o-square-2-stack',
                    'user'  => 'heroicon-o-star',
                ])
                ->iconSize(16)
                ->labels([
                    'admin' => 'Admin',
                    'user'  => 'User',
                ])
                ->panels(fn() => $this->resolvePanels())
                ->canSwitchPanels(fn() => $this->userHasAnyRole())
                ->visible(fn() => $this->userHasAnyRole())
                ->renderHook(PanelsRenderHook::GLOBAL_SEARCH_AFTER);
        });
    }

    /**
     * @return string[]
     */
    private function availableLocales(): array
    {
        return LanguageService::getAvailableLocales();
    }

    /** @return string[] */
    private function resolvePanels(): array
    {
        /** @var User|null $user */
        $user = filament()->auth()->user();

        if ( ! $user) {
            return ['user'];
        }

        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return ['admin', 'user'];
        }

        $panels = ['user'];

        return $panels;
    }

    private function userHasAnyRole(): bool
    {
        /** @var User|null $user */
        $user = filament()->auth()->user();

        if ( ! $user) {
            return false;
        }

        return $user->hasAnyRole($this->panelRoles);
    }
}
