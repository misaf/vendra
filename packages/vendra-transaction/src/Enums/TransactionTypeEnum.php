<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TransactionTypeEnum: string implements HasColor, HasIcon, HasLabel
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case Commission = 'commission';
    case Bonus = 'bonus';
    case Transfer = 'transfer';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * The sign applied to ledger entries posted against the source wallet.
     */
    public function ledgerSign(): int
    {
        return match ($this) {
            self::Deposit, self::Commission, self::Bonus => 1,
            self::Withdrawal, self::Transfer => -1,
        };
    }

    /**
     * @return array<string>
     */
    public function getColor(): array
    {
        return match ($this) {
            self::Deposit => Color::Green,
            self::Withdrawal => Color::Red,
            self::Commission => Color::Green,
            self::Bonus => Color::Purple,
            self::Transfer => Color::Blue,
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Deposit => Heroicon::OutlinedArrowDownTray,
            self::Withdrawal => Heroicon::OutlinedArrowUpTray,
            self::Commission => Heroicon::OutlinedReceiptPercent,
            self::Bonus => Heroicon::OutlinedTrophy,
            self::Transfer => Heroicon::OutlinedArrowsRightLeft,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Deposit => __('vendra-transaction::enums.transaction_type_deposit'),
            self::Withdrawal => __('vendra-transaction::enums.transaction_type_withdrawal'),
            self::Commission => __('vendra-transaction::enums.transaction_type_commission'),
            self::Bonus => __('vendra-transaction::enums.transaction_type_bonus'),
            self::Transfer => __('vendra-transaction::enums.transaction_type_transfer'),
        };
    }
}
