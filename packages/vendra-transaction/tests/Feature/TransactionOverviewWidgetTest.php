<?php

declare(strict_types=1);

use Filament\Widgets\StatsOverviewWidget\Stat;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\TransactionBonusOverviewWidget;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\TransactionDepositOverviewWidget;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\TransactionWithdrawalOverviewWidget;

it('keeps transaction resource stats available', function (string $widget, string $label): void {
    app()->setLocale('en');
    makeCurrentTestTenant();

    /** @var array<int, Stat> $stats */
    $stats = (new ReflectionMethod($widget, 'getStats'))->invoke(app($widget));

    expect($stats)->toHaveCount(1)
        ->and($stats[0]->getLabel())->toBe($label)
        ->and($stats[0]->getChart())->not->toBeEmpty();
})->with([
    'deposit' => [TransactionDepositOverviewWidget::class, 'Deposits'],
    'withdrawal' => [TransactionWithdrawalOverviewWidget::class, 'Withdrawals'],
    'bonus' => [TransactionBonusOverviewWidget::class, 'Bonuses'],
]);
