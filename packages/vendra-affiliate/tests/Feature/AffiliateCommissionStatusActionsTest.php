<?php

declare(strict_types=1);

use Misaf\VendraAffiliate\Actions\ApproveAffiliateCommissionAction;
use Misaf\VendraAffiliate\Actions\ReverseAffiliateCommissionAction;
use Misaf\VendraAffiliate\Database\Factories\AffiliateCommissionFactory;
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Pages\ListAffiliateCommissions;
use Misaf\VendraAffiliate\Models\AffiliateCommission;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();
});

it('approves a pending commission and reverses an approved one from the table', function (): void {
    $pending = AffiliateCommissionFactory::new()->pending()->createOne();
    $approved = AffiliateCommissionFactory::new()->approved()->createOne();

    livewire(ListAffiliateCommissions::class)
        ->callTableAction('approve', $pending)
        ->callTableAction('reverse', $approved)
        ->assertHasNoTableActionErrors();

    expect($pending->refresh()->status)->toBe(CommissionStatusEnum::Approved)
        ->and($approved->refresh()->status)->toBe(CommissionStatusEnum::Reversed);
});

it('refuses to reverse a commission that was paid out after it was loaded', function (): void {
    $commission = AffiliateCommissionFactory::new()->approved()->createOne();
    $staleCommission = AffiliateCommission::query()->findOrFail($commission->id);

    $commission->update(['status' => CommissionStatusEnum::Paid]);

    expect(fn (): AffiliateCommission => resolve(ReverseAffiliateCommissionAction::class)->execute($staleCommission))
        ->toThrow(LogicException::class)
        ->and($commission->refresh()->status)->toBe(CommissionStatusEnum::Paid);
});

it('refuses to approve a commission that is no longer pending', function (): void {
    $commission = AffiliateCommissionFactory::new()->reversed()->createOne();

    expect(fn (): AffiliateCommission => resolve(ApproveAffiliateCommissionAction::class)->execute($commission))
        ->toThrow(LogicException::class)
        ->and($commission->refresh()->status)->toBe(CommissionStatusEnum::Reversed);
});
