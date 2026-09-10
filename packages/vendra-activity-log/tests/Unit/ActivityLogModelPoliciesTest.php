<?php

declare(strict_types=1);

use Misaf\VendraActivityLog\Enums\ActivityLogPolicyEnum;
use Misaf\VendraActivityLog\Models\ActivityLog;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

it('applies shared tenant ownership to the activity log model', function (): void {
    expect(class_uses_recursive(ActivityLog::class))->toContain(BelongsToTenant::class);
});

it('hides the tenant association from activity log serialization', function (): void {
    expect((new ActivityLog)->getHidden())->toContain('tenant_id');
});

it('defines policy permissions for the activity log resource', function (): void {
    $permissions = array_column(ActivityLogPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(10);
});

it('uses kebab-case permission names scoped per model', function (): void {
    $permissions = array_column(ActivityLogPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(count(array_unique($permissions)))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');
});
