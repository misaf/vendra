<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraVerification\Enums\VerificationPolicyEnum;
use Misaf\VendraVerification\Models\Verification;

it('applies shared tenant ownership and soft deletes to the verification model', function (): void {
    expect(class_uses_recursive(Verification::class))->toContain(BelongsToTenant::class, SoftDeletes::class)
        ->and((new Verification)->getHidden())->toContain('tenant_id');
});

it('keeps provider-neutral verification fields fillable and defaults to pending', function (): void {
    expect((new Verification)->getFillable())->toContain(
        'user_profile_id',
        'type',
        'status',
        'provider',
        'country_code',
        'metadata',
    )->and((new Verification)->status)->toBe('pending');
});

it('defines the user profile relationship', function (): void {
    expect(new ReflectionMethod(Verification::class, 'userProfile')->getReturnType()?->getName())->toBe(BelongsTo::class);
});

it('defines policy permissions for the verification resource', function (): void {
    $permissions = array_column(VerificationPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(10)
        ->toHaveSameSize(array_unique($permissions))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');
});
