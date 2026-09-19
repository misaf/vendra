<?php

declare(strict_types=1);

use Misaf\VendraAffiliate\Actions\RecordCartConversionAction;
use Misaf\VendraAffiliate\Database\Factories\AffiliateFactory;
use Misaf\VendraAffiliate\Database\Factories\AffiliateReferralFactory;
use Misaf\VendraAffiliate\Enums\ConversionTypeEnum;
use Misaf\VendraAffiliate\Models\AffiliateCommission;
use Misaf\VendraUser\Models\User;

beforeEach(function (): void {
    makeCurrentTestTenant();
    config()->set('vendra-affiliate.conversions.checkout.enabled', true);
});

it('credits the affiliate who referred the buyer', function (): void {
    $affiliate = AffiliateFactory::new()->active()->state(['commission_percent' => 10])->create();
    $buyer = User::factory()->create();
    AffiliateReferralFactory::new()->forAffiliate($affiliate)->forUser($buyer)->create();
    $order = User::factory()->create();

    $commission = resolve(RecordCartConversionAction::class)->execute($buyer, $order, 25_000);

    expect($commission)->toBeInstanceOf(AffiliateCommission::class)
        ->and($commission?->affiliate_id)->toBe($affiliate->id)
        ->and($commission?->conversion_type)->toBe(ConversionTypeEnum::Checkout)
        ->and($commission?->amount)->toBe(2_500);
});

it('credits nothing when the buyer was not referred or the affiliate is gone', function (bool $referred): void {
    $buyer = User::factory()->create();

    if ($referred) {
        $affiliate = AffiliateFactory::new()->active()->create();
        AffiliateReferralFactory::new()->forAffiliate($affiliate)->forUser($buyer)->create();
        $affiliate->delete();
    }

    expect(resolve(RecordCartConversionAction::class)->execute($buyer, User::factory()->create(), 25_000))->toBeNull()
        ->and(AffiliateCommission::query()->count())->toBe(0);
})->with([
    'not referred' => [false],
    'affiliate deleted' => [true],
]);
