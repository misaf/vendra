<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\States;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraOrder\Models\Order;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * An order's state: Pending until paid, then Confirmed, ending in Completed or Cancelled.
 *
 * @extends State<Order>
 */
abstract class OrderState extends State implements HasColor, HasIcon, HasLabel
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->allowTransition(Pending::class, Confirmed::class)
            ->allowTransition(Confirmed::class, Completed::class)
            ->allowTransition([Pending::class, Confirmed::class], Cancelled::class, CancelOrderTransition::class);
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
