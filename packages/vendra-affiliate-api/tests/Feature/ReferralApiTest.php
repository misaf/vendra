<?php

declare(strict_types=1);

use Misaf\VendraAffiliate\Database\Factories\AffiliateFactory;
use Misaf\VendraAffiliate\Models\AffiliateClick;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('exposes active referral codes through a filtered paginated collection', function (): void {
    $matching = AffiliateFactory::new()->active()->withSignupBounty(1000)->create();
    AffiliateFactory::new()->active()->count(2)->create();
    AffiliateFactory::new()->suspended()->create();

    $response = $this->getJson(
        "/api/marketing/affiliates?code={$matching->code}&itemsPerPage=1",
        ['Accept' => 'application/ld+json'],
    );

    $response->assertOk()
        ->assertJsonPath('totalItems', 1)
        ->assertJsonPath('member.0.id', $matching->id)
        ->assertJsonPath('member.0.code', $matching->code);

    expect(array_keys($response->json('member.0')))
        ->toContain('@id', '@type', 'id', 'userId', 'code', 'commissionPercent', 'signupBounty', 'status', 'createdAt');
});

it('validates filter parameters', function (): void {
    $this->getJson('/api/marketing/affiliates?itemsPerPage=0', ['Accept' => 'application/ld+json'])
        ->assertUnprocessable();
});

it('validates and processes referral visits through the domain action', function (): void {
    $affiliate = AffiliateFactory::new()->active()->create();

    $this->postJson('/api/marketing/affiliate-clicks', [
        'code' => $affiliate->code,
        'landingUrl' => 'https://shop.test/products/1',
    ])->assertNoContent();

    expect(AffiliateClick::query()->whereBelongsTo($affiliate)->count())->toBe(1);

    $this->postJson('/api/marketing/affiliate-clicks', [
        'code' => '',
        'landingUrl' => 'not-a-url',
    ])->assertUnprocessable();
});

it('rejects landing URLs that cannot be stored without truncation', function (): void {
    $affiliate = AffiliateFactory::new()->active()->create();
    $landingUrl = 'https://shop.test/'.str_repeat('a', 240);

    $this->postJson('/api/marketing/affiliate-clicks', [
        'code' => $affiliate->code,
        'landingUrl' => $landingUrl,
    ])->assertUnprocessable();

    expect(AffiliateClick::query()->whereBelongsTo($affiliate)->count())->toBe(0);
});
