<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\NullTenantResolver;
use Misaf\VendraTesting\Tests\Feature\HelpersTestUser;

beforeEach(function (): void {
    $this->app->instance(TenantResolver::class, new NullTenantResolver);
});

it('keeps tenant helpers a no-op when tenancy is disabled', function (): void {
    expect(createTestTenant())->toBeNull()
        ->and(makeCurrentTestTenant())->toBeNull()
        ->and(currentTestTenant())->toBeNull()
        ->and(testTenantModel())->toBe(Model::class);

    switchToTestTenant(null);
    forgetCurrentTestTenant();

    expect(currentTestTenant())->toBeNull();
});

it('resolves the user model from the auth configuration', function (): void {
    config()->set('auth.providers.users.model', HelpersTestUser::class);

    expect(testUserModel())->toBe(HelpersTestUser::class);
});
