<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Models\TransactionLimit;

final class TransactionLimitOverviewWidget extends StatsOverviewWidget
{
    public ?Model $record = null;

    /**
     * @var int|string|array<string, int|null>
     */
    protected int|string|array $columnSpan = [
        'sm' => 1,
    ];

    protected ?string $pollingInterval = null;

    protected function getColumns(): int
    {
        return 1;
    }

    protected static ?int $sort = 5;

    public static function isDiscovered(): bool
    {
        return true;
    }

    public static function canView(): bool
    {
        return true;
    }

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $startOfWeek = now()->startOfWeek(6);
        $endOfWeek = now()->endOfWeek();

        $withdrawalTransactionStats = Trend::query($this->withdrawalLimits())
            ->between($startOfWeek, $endOfWeek)
            ->perDay()
            ->sum('amount');

        $totalWithdrawalAmount = (int) $this->withdrawalLimits()->sum('amount');

        $transactionWithdrawal = Stat::make('withdrawal_transaction_stats', Number::format($totalWithdrawalAmount))
            ->label(__('vendra-transaction::widgets.withdrawal_transaction_stats'))
            ->description(__('vendra-transaction::widgets.withdrawal_transaction_stats_description'))
            ->descriptionIcon(Heroicon::ArrowTrendingUp)
            ->chart($withdrawalTransactionStats->map(fn (TrendValue $value) => $value->aggregate)->toArray())
            ->color('primary');

        return [$transactionWithdrawal];
    }

    /**
     * Get the withdrawal limits, narrowed to the viewed user's wallets on a user's page.
     *
     * @return Builder<TransactionLimit>
     */
    private function withdrawalLimits(): Builder
    {
        $limits = TransactionLimit::query()->ofType(TransactionTypeEnum::Withdrawal);

        return $this->record instanceof Model ? $limits->ownedBy($this->record) : $limits;
    }
}
