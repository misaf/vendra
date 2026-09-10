<?php

declare(strict_types=1);

use Misaf\VendraAffiliate\Enums\AffiliateClickPolicyEnum;
use Misaf\VendraAffiliate\Enums\AffiliateCommissionPolicyEnum;
use Misaf\VendraAffiliate\Enums\AffiliatePayoutPolicyEnum;
use Misaf\VendraAffiliate\Enums\AffiliatePolicyEnum;
use Misaf\VendraAffiliate\Enums\AffiliateReferralPolicyEnum;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraAffiliate\Models\AffiliateClick;
use Misaf\VendraAffiliate\Models\AffiliateCommission;
use Misaf\VendraAffiliate\Models\AffiliatePayout;
use Misaf\VendraAffiliate\Models\AffiliateReferral;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

it('applies shared tenant ownership to affiliate models', function (string $model): void {
    expect(class_uses_recursive($model))->toContain(BelongsToTenant::class);
})->with([
    Affiliate::class,
    AffiliateClick::class,
    AffiliateReferral::class,
    AffiliateCommission::class,
    AffiliatePayout::class,
]);

it('defines policy permissions for all affiliate resources', function (): void {
    expect(array_column(AffiliatePolicyEnum::cases(), 'value'))->toHaveCount(10)
        ->and(array_column(AffiliateClickPolicyEnum::cases(), 'value'))->toHaveCount(4)
        ->and(array_column(AffiliateReferralPolicyEnum::cases(), 'value'))->toHaveCount(5)
        ->and(array_column(AffiliateCommissionPolicyEnum::cases(), 'value'))->toHaveCount(8)
        ->and(array_column(AffiliatePayoutPolicyEnum::cases(), 'value'))->toHaveCount(3);
});

it('uses kebab-case permission names scoped per model', function (): void {
    $permissions = [
        ...array_column(AffiliatePolicyEnum::cases(), 'value'),
        ...array_column(AffiliateClickPolicyEnum::cases(), 'value'),
        ...array_column(AffiliateReferralPolicyEnum::cases(), 'value'),
        ...array_column(AffiliateCommissionPolicyEnum::cases(), 'value'),
        ...array_column(AffiliatePayoutPolicyEnum::cases(), 'value'),
    ];

    expect($permissions)->toHaveSameSize(array_unique($permissions))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');
});

it('keeps affiliate click records immutable', function (): void {
    expect(AffiliateClick::UPDATED_AT)->toBeNull();
});
