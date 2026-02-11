<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use Filament\Tables\Columns\SpatieTagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Str;
use Misaf\VendraTransaction\Models\Transaction;

final class LatestTransactionTableWidget extends BaseWidget
{
    protected static ?int $sort = 7;

    /**
     * @var int|string|array<string, int|null>
     */
    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 2,
        'lg' => 4,
    ];

    protected function getColumns(): int
    {
        return 1;
    }

    public static function isDiscovered(): bool
    {
        return true;
    }

    public static function canView(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('transaction::widgets.latest_transaction_table'))
            ->query(Transaction::take(10))
            ->columns([
                TextColumn::make('transactionGateway.name')
                    ->label(__('model.transaction_gateway')),

                TextColumn::make('transaction_type')
                    ->badge()
                    ->label(__('transaction::attributes.transaction_type')),

                TextColumn::make('user.username')
                    ->label(__('form.username')),

                TextColumn::make('token')
                    ->alignCenter()
                    ->copyable()
                    ->copyMessage(__('form.token_saved'))
                    ->copyMessageDuration(1500)
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->formatStateUsing(function (string $state): string {
                        return Str::of($state)->split(4)->implode(' ');
                    })
                    ->label(__('transaction.token')),

                TextColumn::make('amount')
                    ->alignCenter()
                    ->copyable()
                    ->copyMessage(__('Amount copied to clipboard'))
                    ->copyMessageDuration(1500)
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('transaction::attributes.amount'))
                    ->numeric(locale: 'en', maxDecimalPlaces: 0),

                TextColumn::make('status')
                    ->alignStart()
                    ->label(__('transaction::attributes.status')),

                SpatieTagsColumn::make('tags')
                    ->label(__('tag::navigation.tag')),

                TextColumn::make('created_at')
                    ->label(__('transaction::attributes.status')),
            ])
            ->paginated(false)
            ->searchable(false)
            ->poll('10s');
    }
}
