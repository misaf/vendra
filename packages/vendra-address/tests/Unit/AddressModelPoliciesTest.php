<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Misaf\VendraAddress\Enums\AddressPolicyEnum;
use Misaf\VendraAddress\Models\Address;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

it('applies shared tenant ownership and soft deletes to the address model', function (): void {
    expect(class_uses_recursive(Address::class))->toContain(BelongsToTenant::class, SoftDeletes::class)
        ->and((new Address)->getHidden())->toContain('tenant_id');
});

it('keeps country-adaptable address fields fillable', function (): void {
    expect((new Address)->getFillable())->toContain(
        'user_profile_id',
        'line_one',
        'line_two',
        'line_three',
        'locality',
        'administrative_area',
        'sorting_code',
        'country_code',
        'locale',
        'metadata',
        'notes',
    );
});

it('defines the user profile relationship', function (): void {
    expect((new ReflectionMethod(Address::class, 'userProfile'))->getReturnType()?->getName())->toBe(BelongsTo::class);
});

it('defines policy permissions for the address resource', function (): void {
    $permissions = array_column(AddressPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(10)
        ->toHaveCount(count(array_unique($permissions)))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');
});
