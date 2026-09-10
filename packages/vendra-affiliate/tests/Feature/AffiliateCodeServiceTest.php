<?php

declare(strict_types=1);

use Misaf\VendraAffiliate\Database\Factories\AffiliateFactory;
use Misaf\VendraAffiliate\Services\AffiliateCodeService;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('generates codes from the unambiguous alphabet', function (): void {
    $code = resolve(AffiliateCodeService::class)->generate();

    expect($code)->toHaveLength(8)
        ->toMatch('/^[23456789ABCDEFGHJKMNPQRSTUVWXYZ]{8}$/');
});

it('assigns a generated code when an affiliate is created without one', function (): void {
    $affiliate = AffiliateFactory::new()
        ->state(['code' => null])
        ->create();

    expect($affiliate->code)->not->toBeNull()
        ->toMatch('/^[23456789ABCDEFGHJKMNPQRSTUVWXYZ]{8}$/');
});

it('keeps an explicitly provided code', function (): void {
    $affiliate = AffiliateFactory::new()
        ->state(['code' => 'MYCODE22'])
        ->create();

    expect($affiliate->code)->toBe('MYCODE22');
});
