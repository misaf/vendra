<?php

declare(strict_types=1);

use App\Filament\Admin\Pages\Dashboard as AdminDashboard;
use Filament\Facades\Filament;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Livewire;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Misaf\VendraActivityLog\Filament\Widgets\LatestActivityLogTableWidget;
use Misaf\VendraAffiliate\Filament\Widgets\AffiliateOverviewWidget;
use Misaf\VendraMultimedia\Filament\Widgets\LatestMultimediaTableWidget;
use Misaf\VendraProduct\Filament\Widgets\ProductOverviewWidget;
use Misaf\VendraTransaction\Filament\Widgets\LatestTransactionTableWidget;
use Misaf\VendraTransaction\Filament\Widgets\TransactionTypeChartWidget;
use Misaf\VendraUser\Filament\Widgets\LatestUsersWidget;

it('orders admin dashboard widgets by unique priority', function (): void {
    $expectedWidgets = [
        ProductOverviewWidget::class,
        AffiliateOverviewWidget::class,
        LatestTransactionTableWidget::class,
        LatestUsersWidget::class,
        LatestActivityLogTableWidget::class,
        LatestMultimediaTableWidget::class,
    ];

    $registeredWidgets = array_values(array_filter(
        Filament::getPanel('admin')->getWidgets(),
        fn (mixed $widget): bool => is_string($widget) && in_array($widget, $expectedWidgets, true),
    ));

    expect($registeredWidgets)->toBe($expectedWidgets)
        ->and(array_map(
            fn (string $widget): int => $widget::getSort(),
            $expectedWidgets,
        ))->toBe(range(1, 6));
});

it('uses the registered widgets directly on the admin dashboard', function (): void {
    $currentPanel = Filament::getCurrentPanel();

    try {
        Filament::setCurrentPanel('admin');

        expect(app(AdminDashboard::class)->getWidgets())->toBe(Filament::getWidgets());
    } finally {
        Filament::setCurrentPanel($currentPanel);
    }
});

it('does not poll dashboard widgets', function (): void {
    $statsWidgets = [
        ProductOverviewWidget::class,
        TransactionTypeChartWidget::class,
        AffiliateOverviewWidget::class,
    ];

    foreach ($statsWidgets as $widgetClass) {
        $pollingInterval = (new ReflectionMethod($widgetClass, 'getPollingInterval'))
            ->invoke(app($widgetClass));

        expect($pollingInterval)->toBeNull();
    }

    $tableWidgets = [
        LatestTransactionTableWidget::class,
        LatestUsersWidget::class,
        LatestActivityLogTableWidget::class,
        LatestMultimediaTableWidget::class,
    ];

    $locale = app()->getLocale();

    try {
        app()->setLocale('fa');

        foreach ($tableWidgets as $widgetClass) {
            /** @var TableWidget $widget */
            $widget = app($widgetClass);

            expect($widget->table(Table::make($widget))->getPollingInterval())->toBeNull();
        }
    } finally {
        app()->setLocale($locale);
    }
});

it('uses consistent dashboard widget widths', function (): void {
    $fullWidthWidgets = [
        ProductOverviewWidget::class,
        AffiliateOverviewWidget::class,
        LatestTransactionTableWidget::class,
        LatestUsersWidget::class,
        LatestActivityLogTableWidget::class,
        LatestMultimediaTableWidget::class,
    ];

    foreach ($fullWidthWidgets as $widgetClass) {
        expect(app($widgetClass)->getColumnSpan())->toBe('full');
    }

});

it('groups all dashboard widgets beside a transaction chart', function (): void {
    $currentPanel = Filament::getCurrentPanel();
    $locale = app()->getLocale();

    try {
        Filament::setCurrentPanel('admin');
        app()->setLocale('en');

        $dashboard = app(AdminDashboard::class);
        $layout = $dashboard->getWidgetsContentComponent();
        $components = $layout->getDefaultChildComponents();
        $dashboardWidgets = $components[0]->getDefaultChildComponents();

        expect($layout)->toBeInstanceOf(Grid::class)
            ->and($layout->getColumns('lg'))->toBe(3)
            ->and($components)->toHaveCount(2)
            ->and($components[0])->toBeInstanceOf(Group::class)
            ->and($components[0]->getColumns('md'))->toBe(3)
            ->and($components[0]->getColumnSpan('lg'))->toBe(2)
            ->and($dashboardWidgets)->not->toBeEmpty()
            ->and(array_map(
                static fn (Component $component): array|int|null => $component->getColumnOrder('default'),
                $dashboardWidgets,
            ))->toBe(range(count($dashboardWidgets), 1))
            ->and(array_map(
                static fn (Component $component): array|int|null => $component->getColumnOrder('md'),
                $dashboardWidgets,
            ))->toBe(range(1, count($dashboardWidgets)))
            ->and(array_filter(
                $dashboardWidgets,
                static fn (Component $component): bool => $component->isLiberatedFromContainerGrid(),
            ))->toBeEmpty()
            ->and($components[1])->toBeInstanceOf(Livewire::class)
            ->and($components[1]->getComponent())->toBe(TransactionTypeChartWidget::class)
            ->and($components[1]->getExtraAttributeBag()->get('class'))->toBe('max-md:order-last')
            ->and($components[1]->getColumnSpan('lg'))->toBe(1);
    } finally {
        app()->setLocale($locale);
        Filament::setCurrentPanel($currentPanel);
    }
});
