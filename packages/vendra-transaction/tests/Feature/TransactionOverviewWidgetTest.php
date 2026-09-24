<?php

declare(strict_types=1);

use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Number;
use Misaf\VendraTransaction\Database\Factories\TransactionFactory;
use Misaf\VendraTransaction\Database\Factories\TransactionLimitFactory;
use Misaf\VendraTransaction\Database\Factories\WalletFactory;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\TransactionBonusOverviewWidget;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\TransactionDepositOverviewWidget;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\TransactionLimitOverviewWidget;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\TransactionWithdrawalOverviewWidget;

it('keeps transaction resource stats available', function (string $widget, string $label): void {
    app()->setLocale('en');
    makeCurrentTestTenant();

    /** @var array<int, Stat> $stats */
    $stats = new ReflectionMethod($widget, 'getStats')->invoke(resolve($widget));

    expect($stats)->toHaveCount(1)
        ->and(Arr::get($stats, 0)->getLabel())->toBe($label)
        ->and(Arr::get($stats, 0)->getChart())->not->toBeEmpty();
})->with([
    'deposit' => [TransactionDepositOverviewWidget::class, 'Deposits'],
    'withdrawal' => [TransactionWithdrawalOverviewWidget::class, 'Withdrawals'],
    'bonus' => [TransactionBonusOverviewWidget::class, 'Bonuses'],
]);

it("totals only the viewed user's wallets on a user's page", function (): void {
    app()->setLocale('en');
    makeCurrentTestTenant();

    $wallet = WalletFactory::new()->create();
    $otherWallet = WalletFactory::new()->create();
    TransactionFactory::new()->forWallet($wallet)->deposit()->approved()->create(['amount' => 4_000]);
    TransactionFactory::new()->forWallet($otherWallet)->deposit()->approved()->create(['amount' => 9_000]);
    TransactionLimitFactory::new()->forWallet($wallet)->ofType(TransactionTypeEnum::Withdrawal)->create(['amount' => 1_000]);
    TransactionLimitFactory::new()->forWallet($otherWallet)->ofType(TransactionTypeEnum::Withdrawal)->create(['amount' => 5_000]);

    $total = function (string $widget, ?Model $record): string|int|float|null {
        $instance = resolve($widget);
        $instance->record = $record;

        /** @var array<int, Stat> $stats */
        $stats = new ReflectionMethod($widget, 'getStats')->invoke($instance);

        return Arr::get($stats, 0)->getValue();
    };

    expect($total(TransactionDepositOverviewWidget::class, $wallet->user))->toBe(Number::format(4_000))
        ->and($total(TransactionDepositOverviewWidget::class, null))->toBe(Number::format(13_000))
        ->and($total(TransactionLimitOverviewWidget::class, $wallet->user))->toBe(Number::format(1_000));
});
