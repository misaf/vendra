<?php

declare(strict_types=1);

namespace App\Filament\User\Widgets;

use App\Tables\Columns\CreatedAtTextColumn;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use Misaf\VendraTransaction\Enums\TransactionStatusEnum;
use Misaf\VendraTransaction\Facades\TransactionService;
use Misaf\VendraTransaction\Models\Transaction;

final class LatestTransactionTableWidget extends BaseWidget
{
    use WithRateLimiting;

    /**
     * @var int|string|array<string, int|null>
     */
    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 1;
    }

    protected static ?int $sort = 2;

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
            ->query(
                Transaction::query()
                    ->where('user_id', $this->getAuthenticatedUser()->getAuthIdentifier())
                    ->where('created_at', '>=', now()->subDays(30)),
            )
            ->columns([
                TextColumn::make('transactionGateway.name')
                    ->label(__('model.transaction_gateway')),

                TextColumn::make('transaction_type')
                    ->badge()
                    ->label(__('transaction::attributes.transaction_type')),

                TextColumn::make('token')
                    ->alignCenter()
                    ->copyable()
                    ->copyMessage(__('form.token_saved'))
                    ->copyMessageDuration(1500)
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->formatStateUsing(function (string $state): string {
                        return Str::of($state)->split(4)->implode(' ');
                    })
                    ->label(__('transaction.token'))
                    ->searchable(),

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

                CreatedAtTextColumn::make('created_at')
                    ->label(__('transaction::attributes.status')),
            ])
            ->recordActions([
                Action::make(TransactionStatusEnum::Declined->value)
                    ->action(function (Transaction $record): void {
                        try {
                            $this->rateLimit(1);

                            TransactionService::updateTransactionStatus($record, TransactionStatusEnum::Declined);
                        } catch (TooManyRequestsException $exception) {
                            $this->sendRateLimitNotification($exception);
                        }
                    })
                    ->button()
                    ->color('danger')
                    ->icon('heroicon-s-no-symbol')
                    ->label('برگشت')
                    ->requiresConfirmation()
                    ->size(Size::ExtraSmall)
                    ->visible(function (Transaction $record): bool {
                        return TransactionService::isWithdrawal($record)
                            && TransactionService::isReview($record);
                    }),
            ]);
    }

    /**
     * @return ?User
     */
    private function getAuthenticatedUser(): ?Authenticatable
    {
        return filament()->auth()->user();
    }

    private function sendRateLimitNotification(TooManyRequestsException $exception): void
    {
        Notification::make()
            ->title(__('شما بیش از حد مجاز درخواست خرید داشته‌اید'))
            ->body(__('لطفا :seconds ثانیه دیگر صبر نمایید.', ['seconds' => $exception->secondsUntilAvailable]))
            ->danger()
            ->send();
    }
}
