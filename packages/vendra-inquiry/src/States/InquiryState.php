<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\States;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraInquiry\Models\Inquiry;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * An inquiry's state: Open until answered, and closable or reopenable from any other state.
 *
 * @extends State<Inquiry>
 */
abstract class InquiryState extends State implements HasColor, HasIcon, HasLabel
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Open::class)
            ->allowTransition([Open::class, Closed::class], Answered::class, AnswerInquiryTransition::class)
            ->allowTransition([Open::class, Answered::class], Closed::class)
            ->allowTransition([Answered::class, Closed::class], Open::class, ReopenInquiryTransition::class);
    }

    /**
     * Get the states as select options, keyed by their stored value.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::all() as $state) {
            if (! is_string($state) || ! is_subclass_of($state, self::class)) {
                continue;
            }

            $options[$state::getMorphClass()] = new $state(new Inquiry)->getLabel();
        }

        return $options;
    }

    /**
     * @return array<string>
     */
    abstract public function getColor(): array;

    abstract public function getIcon(): Heroicon;

    abstract public function getLabel(): string;
}
