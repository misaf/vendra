<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Misaf\VendraPhone\Enums\PhoneNumberPolicyEnum;
use Misaf\VendraPhone\Models\PhoneNumber;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

it('applies shared tenant ownership and soft deletes to the phone number model', function (): void {
    expect(class_uses_recursive(PhoneNumber::class))->toContain(BelongsToTenant::class, SoftDeletes::class)
        ->and((new PhoneNumber)->getHidden())->toContain('tenant_id');
});

it('keeps international phone fields fillable', function (): void {
    expect((new PhoneNumber)->getFillable())->toContain(
        'user_profile_id',
        'type',
        'country_code',
        'number',
        'extension',
        'metadata',
        'notes',
        'is_primary',
    );
});

it('defines the user profile relationship', function (): void {
    expect((new ReflectionMethod(PhoneNumber::class, 'userProfile'))->getReturnType()?->getName())->toBe(BelongsTo::class);
});

it('defines policy permissions for the phone number resource', function (): void {
    $permissions = array_column(PhoneNumberPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(10)
        ->toHaveCount(count(array_unique($permissions)))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');
});
