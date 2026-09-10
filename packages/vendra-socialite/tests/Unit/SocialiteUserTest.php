<?php

declare(strict_types=1);

use DutchCodingCompany\FilamentSocialite\Models\Contracts\FilamentSocialiteUser;
use Misaf\VendraSocialite\Models\SocialiteUser;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

it('is a tenant-aware filament socialite identity', function (): void {
    expect(class_uses_recursive(SocialiteUser::class))->toContain(BelongsToTenant::class)
        ->and(new SocialiteUser)->toBeInstanceOf(FilamentSocialiteUser::class)
        ->and((new SocialiteUser)->getTable())->toBe('socialite_users');
});

it('overrides the base finders so they resolve the tenant-aware subclass', function (): void {
    $find = new ReflectionMethod(SocialiteUser::class, 'findForProvider');
    $create = new ReflectionMethod(SocialiteUser::class, 'createForProvider');

    expect($find->getDeclaringClass()->getName())->toBe(SocialiteUser::class)
        ->and($create->getDeclaringClass()->getName())->toBe(SocialiteUser::class);
});
