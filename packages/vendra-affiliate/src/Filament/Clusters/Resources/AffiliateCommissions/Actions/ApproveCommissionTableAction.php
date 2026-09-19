<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use LogicException;
use Misaf\VendraAffiliate\Actions\ApproveAffiliateCommissionAction;
use Misaf\VendraAffiliate\Enums\AffiliateCommissionPolicyEnum;
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Models\AffiliateCommission;

final class ApproveCommissionTableAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'approve';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->authorize(fn (): bool => (bool) auth()->user()?->can(AffiliateCommissionPolicyEnum::Approve->value))
            ->color('success')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->label(__('vendra-affiliate::messages.approve_commission'))
            ->requiresConfirmation()
            ->visible(fn (AffiliateCommission $record): bool => $record->status === CommissionStatusEnum::Pending)
            ->action(function (AffiliateCommission $record, ApproveAffiliateCommissionAction $approveAffiliateCommissionAction): void {
                try {
                    $approveAffiliateCommissionAction->execute($record);
                } catch (LogicException) {
                    Notification::make()
                        ->danger()
                        ->title(__('vendra-affiliate::messages.commission_status_changed'))
                        ->send();
                }
            });
    }
}
