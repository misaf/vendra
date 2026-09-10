<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\States;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraTransaction\Models\Transaction;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * Lifecycle of a transaction: it enters as Pending, may pass through
 * Processing and Review, and terminates in Approved (settled to the
 * ledger), Declined, or Failed.
 *
 * @extends State<Transaction>
 */
abstract class TransactionState extends State implements HasColor, HasIcon, HasLabel
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->allowTransition(Pending::class, Processing::class)
            ->allowTransition([Pending::class, Processing::class], Review::class)
            ->allowTransition([Pending::class, Processing::class, Review::class], Approved::class, ApproveTransactionTransition::class)
            ->allowTransition([Pending::class, Processing::class, Review::class], Declined::class)
            ->allowTransition([Pending::class, Processing::class, Review::class], Failed::class);
    }

    /**
     * Whether the state is terminal and allows no further transitions.
     */
    public function isFinal(): bool
    {
        return false;
    }

    /**
     * @return array<string>
     */
    abstract public function getColor(): array;

    abstract public function getIcon(): Heroicon;

    abstract public function getLabel(): string;
}
