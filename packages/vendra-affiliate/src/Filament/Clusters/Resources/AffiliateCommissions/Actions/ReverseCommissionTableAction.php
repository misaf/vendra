<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use LogicException;
use Misaf\VendraAffiliate\Actions\ReverseAffiliateCommissionAction;
use Misaf\VendraAffiliate\Enums\AffiliateCommissionPolicyEnum;
use Misaf\VendraAffiliate\Models\AffiliateCommission;

final class ReverseCommissionTableAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'reverse';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->authorize(fn (): bool => (bool) auth()->user()?->can(AffiliateCommissionPolicyEnum::Reverse->value))
            ->color('danger')
            ->icon(Heroicon::OutlinedArrowUturnLeft)
            ->label(__('vendra-affiliate::messages.reverse_commission'))
            ->requiresConfirmation()
            ->visible(fn (AffiliateCommission $record): bool => $record->canBeReversed())
            ->action(function (AffiliateCommission $record, ReverseAffiliateCommissionAction $reverseAffiliateCommissionAction): void {
                try {
                    $reverseAffiliateCommissionAction->execute($record);
                } catch (LogicException) {
                    Notification::make()
                        ->danger()
                        ->title(__('vendra-affiliate::messages.commission_status_changed'))
                        ->send();
                }
            });
    }
}
