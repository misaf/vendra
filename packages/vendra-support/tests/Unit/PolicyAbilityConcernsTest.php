<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Unit;

use Error;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Misaf\VendraSupport\Authorization\AuthorizesUpdateAbilities;
use Misaf\VendraSupport\Authorization\ResolvesPolicyPermissions;
use Mockery;

it('authorizes trait abilities through the policy permission enum', function (): void {
    $policy = new SupportTestPolicy;
    $model = new SupportTestTag;

    $user = Mockery::mock(Authorizable::class);
    $user->shouldReceive('can')->with('view-any-support-test')->andReturnTrue();
    $user->shouldReceive('can')->with('view-support-test')->andReturnFalse();
    $user->shouldReceive('can')->with('delete-support-test')->andReturnTrue();
    $user->shouldReceive('can')->with('delete-any-support-test')->andReturnFalse();

    expect($policy->viewAny($user))->toBeTrue()
        ->and($policy->view($user, $model))->toBeFalse()
        ->and($policy->delete($user, $model))->toBeTrue()
        ->and($policy->deleteAny($user))->toBeFalse();
});

it('fails loudly when a composed ability has no matching enum case', function (): void {
    $policy = new class
    {
        use AuthorizesUpdateAbilities;
        use ResolvesPolicyPermissions;

        protected static function permissionEnum(): string
        {
            return SupportTestPolicyEnum::class;
        }
    };

    $user = Mockery::mock(Authorizable::class);

    expect(fn (): bool => $policy->update($user, new SupportTestTag))->toThrow(Error::class);
});
