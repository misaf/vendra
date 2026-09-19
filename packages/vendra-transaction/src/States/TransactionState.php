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
 * A transaction's state: Pending, Processing, or Review, ending in Approved, Declined, or Failed.
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
            ->allowTransition([Pending::class, Processing::class, Review::class], Declined::class, DeclineTransactionTransition::class)
            ->allowTransition([Pending::class, Processing::class, Review::class], Failed::class, FailTransactionTransition::class);
    }

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
