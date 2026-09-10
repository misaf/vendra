<?php

declare(strict_types=1);

use Misaf\VendraAffiliate\Actions\AttributeReferralAction;
use Misaf\VendraAffiliate\Database\Factories\AffiliateFactory;
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Enums\ConversionTypeEnum;
use Misaf\VendraAffiliate\Models\AffiliateCommission;
use Misaf\VendraAffiliate\Models\AffiliateReferral;
use Misaf\VendraUser\Models\User;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('attributes a referred user to the affiliate behind the code', function (): void {
    $affiliate = AffiliateFactory::new()->active()->create();
    $user = User::factory()->create();

    $referral = resolve(AttributeReferralAction::class)->execute($affiliate->code, $user);

    expect($referral)->toBeInstanceOf(AffiliateReferral::class)
        ->and($referral->affiliate_id)->toBe($affiliate->id)
        ->and($referral->user_id)->toBe($user->id);
});

it('ignores unknown and suspended affiliate codes', function (): void {
    $suspended = AffiliateFactory::new()->suspended()->create();
    $user = User::factory()->create();

    expect(resolve(AttributeReferralAction::class)->execute('NOPE1234', $user))->toBeNull()
        ->and(resolve(AttributeReferralAction::class)->execute($suspended->code, $user))->toBeNull()
        ->and(AffiliateReferral::query()->count())->toBe(0);
});

it('rejects self-referrals', function (): void {
    $affiliate = AffiliateFactory::new()->active()->create();

    $referral = resolve(AttributeReferralAction::class)->execute($affiliate->code, $affiliate->user);

    expect($referral)->toBeNull()
        ->and(AffiliateReferral::query()->count())->toBe(0);
});

it('attributes each user at most once', function (): void {
    $first = AffiliateFactory::new()->active()->create();
    $second = AffiliateFactory::new()->active()->create();
    $user = User::factory()->create();

    resolve(AttributeReferralAction::class)->execute($first->code, $user);
    $repeat = resolve(AttributeReferralAction::class)->execute($second->code, $user);

    expect($repeat)->toBeNull()
        ->and(AffiliateReferral::query()->count())->toBe(1)
        ->and(AffiliateReferral::query()->sole()->affiliate_id)->toBe($first->id);
});

it('credits a signup bounty when the signup conversion is enabled', function (): void {
    config()->set('vendra-affiliate.conversions.signup.enabled', true);

    $affiliate = AffiliateFactory::new()->active()->withSignupBounty(500)->create();
    $user = User::factory()->create();

    resolve(AttributeReferralAction::class)->execute($affiliate->code, $user);

    $commission = AffiliateCommission::query()->sole();

    expect($commission->conversion_type)->toBe(ConversionTypeEnum::Signup)
        ->and($commission->amount)->toBe(500)
        ->and($commission->affiliate_id)->toBe($affiliate->id)
        ->and($commission->status)->toBe(CommissionStatusEnum::Approved);
});

it('does not credit a signup bounty when the signup conversion is disabled', function (): void {
    config()->set('vendra-affiliate.conversions.signup.enabled', false);

    $affiliate = AffiliateFactory::new()->active()->withSignupBounty(500)->create();
    $user = User::factory()->create();

    resolve(AttributeReferralAction::class)->execute($affiliate->code, $user);

    expect(AffiliateCommission::query()->count())->toBe(0);
});
