<?php

declare(strict_types=1);

use Misaf\VendraAuthifyLog\Enums\AuthifyLogPolicyEnum;
use Misaf\VendraAuthifyLog\Models\AuthifyLog;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

it('applies shared tenant ownership to the authify log model', function (): void {
    expect(class_uses_recursive(AuthifyLog::class))->toContain(BelongsToTenant::class);
});

it('hides the tenant association from authify log serialization', function (): void {
    expect((new AuthifyLog)->getHidden())->toContain('tenant_id');
});

it('defines policy permissions for the authify log resource', function (): void {
    $permissions = array_column(AuthifyLogPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(10);
});

it('uses kebab-case permission names scoped per model', function (): void {
    $permissions = array_column(AuthifyLogPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveSameSize(array_unique($permissions))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');
});
