<?php

declare(strict_types=1);

use Misaf\VendraAffiliate\Database\Factories\AffiliateFactory;
use Misaf\VendraAffiliate\Models\AffiliateClick;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('records a click, drops the attribution cookie, and redirects', function (): void {
    $affiliate = AffiliateFactory::new()->active()->create();

    $response = $this->get(route('affiliate.redirect', ['code' => $affiliate->code]));

    $click = AffiliateClick::query()->sole();

    $response->assertRedirect(config('vendra-affiliate.attribution.redirect_url'))
        ->assertCookie(
            config()->string('vendra-affiliate.attribution.cookie_name'),
            sprintf('%s|%d', $affiliate->code, $click->id),
        );

    expect($click->affiliate_id)->toBe($affiliate->id)
        ->and($click->ip_address)->not->toBeNull();
});

it('redirects silently without recording anything for unknown codes', function (): void {
    $response = $this->get(route('affiliate.redirect', ['code' => 'UNKNOWN1']));

    $response->assertRedirect(config('vendra-affiliate.attribution.redirect_url'))
        ->assertCookieMissing(config()->string('vendra-affiliate.attribution.cookie_name'));

    expect(AffiliateClick::query()->count())->toBe(0);
});

it('does not track clicks for suspended affiliates', function (): void {
    $affiliate = AffiliateFactory::new()->suspended()->create();

    $this->get(route('affiliate.redirect', ['code' => $affiliate->code]));

    expect(AffiliateClick::query()->count())->toBe(0);
});
