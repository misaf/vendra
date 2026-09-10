<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Config;
use Misaf\VendraSupport\Authorization\AuthorizesSandboxMode;

it('authorizes policy abilities only while sandbox mode is enabled', function (): void {
    $policy = new class
    {
        use AuthorizesSandboxMode;
    };
    $user = Mockery::mock(Authorizable::class);

    Config::set('vendra-support.sandbox', true);

    expect($policy->before($user, 'viewAny'))->toBeTrue();

    Config::set('vendra-support.sandbox', false);

    expect($policy->before($user, 'viewAny'))->toBeNull();
});

it('applies sandbox authorization to every package policy', function (): void {
    $policyFiles = glob(base_path('packages/*/src/Policies/*Policy.php')) ?: [];

    $policiesWithoutSandboxAuthorization = array_values(array_filter(
        $policyFiles,
        fn (string $policyFile): bool => ! str_contains(
            file_get_contents($policyFile) ?: '',
            'use AuthorizesSandboxMode;',
        ),
    ));

    expect($policyFiles)->not->toBeEmpty()
        ->and($policiesWithoutSandboxAuthorization)->toBeEmpty();
});
