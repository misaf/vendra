<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\SoftDeletes;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraUserProfile\Enums\UserProfilePolicyEnum;
use Misaf\VendraUserProfile\Models\UserProfile;

it('applies shared tenant ownership and soft deletes to the user profile model', function (): void {
    expect(class_uses_recursive(UserProfile::class))->toContain(BelongsToTenant::class, SoftDeletes::class);
});

it('hides the tenant association from user profile serialization', function (): void {
    expect((new UserProfile)->getHidden())->toContain('tenant_id');
});

it('defines policy permissions for the user profile resource', function (): void {
    $permissions = array_column(UserProfilePolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(10);
});

it('uses kebab-case permission names scoped per model', function (): void {
    $permissions = array_column(UserProfilePolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(count(array_unique($permissions)))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');
});
