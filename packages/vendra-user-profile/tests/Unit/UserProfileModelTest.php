<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Misaf\VendraUserProfile\Models\UserProfile;
use Misaf\VendraUserProfile\Traits\BelongsToUserProfile;
use Misaf\VendraUserProfile\Traits\HasUserProfile;

it('defines profile relationships for multiple profiles per user', function (): void {
    expect(new ReflectionMethod(HasUserProfile::class, 'profiles')->getReturnType()?->getName())->toBe(HasMany::class)
        ->and(new ReflectionMethod(HasUserProfile::class, 'userProfiles')->getReturnType()?->getName())->toBe(HasMany::class)
        ->and(new ReflectionMethod(UserProfile::class, 'user')->getReturnType()?->getName())->toBe(BelongsTo::class);
});

it('defines the expected profile model attributes', function (): void {
    $profile = new UserProfile;

    expect($profile->getFillable())
        ->toBe(['tenant_id', 'user_id', 'name', 'description', 'slug', 'is_default', 'active'])
        ->and($profile->getHidden())
        ->toBe(['tenant_id', 'active_name_guard', 'active_slug_guard', 'default_user_guard'])
        ->and($profile->getCasts())
        ->toMatchArray([
            'id' => 'integer',
            'tenant_id' => 'integer',
            'user_id' => 'integer',
            'name' => 'string',
        ]);
});

it('defines the inverse user profile trait relationship with the profile package model', function (): void {
    expect(new ReflectionMethod(BelongsToUserProfile::class, 'userProfile')->getReturnType()?->getName())->toBe(BelongsTo::class);
});

it('uses name as the profile full name value', function (): void {
    $profile = new UserProfile([
        'name' => 'Primary Profile',
    ]);

    expect($profile->full_name)->toBe('Primary Profile');
});
