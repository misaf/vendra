<?php

declare(strict_types=1);

namespace App\Filament\User\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

final class TransactionChart extends ChartWidget
{
    protected string $color = 'primary';

    protected ?string $maxHeight = '200px';

    protected function getData(): array
    {
        /** @var User $user */
        $user = filament()->auth()->user();

        $sumDeposits = (int) $user->transactions()
            ->deposit()
            ->approved()
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('amount') ?? 0;
        $sumWithdrawals = abs((int) $user->transactions()
            ->withdrawal()
            ->approved()
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('amount') ?? 0);
        $sumBonuses = (int) $user->transactions()
            ->bonus()
            ->approved()
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('amount') ?? 0;

        $data = [$sumBonuses, $sumWithdrawals, $sumDeposits];

        if (0 === array_sum($data)) {
            $data = [0.01]; // fallback to render an invisible slice
        }

        return [
            'labels' => [
                __('transaction::transaction_type_enum.bonus'),
                __('transaction::transaction_type_enum.withdrawal'),
                __('transaction::transaction_type_enum.deposit'),
            ],
            'datasets' => [
                [
                    'data'        => $data,
                    'borderWidth' => 0.7,
                ],
            ],
        ];
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'تراکنش های من';
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): ?array
    {
        return [
            'responsive' => true,
            'scales'     => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
        ];
    }
}
