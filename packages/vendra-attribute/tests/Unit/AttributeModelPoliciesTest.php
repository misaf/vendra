<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\SoftDeletes;
use Misaf\VendraAttribute\Enums\AttributePolicyEnum;
use Misaf\VendraAttribute\Enums\AttributeValuePolicyEnum;
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraAttribute\Models\AttributeValue;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

it('applies shared tenant ownership and soft deletes to attribute models', function (): void {
    expect(class_uses_recursive(Attribute::class))->toContain(BelongsToTenant::class, SoftDeletes::class)
        ->and(class_uses_recursive(AttributeValue::class))->toContain(BelongsToTenant::class, SoftDeletes::class);
});

it('hides the tenant association from attribute serialization', function (): void {
    expect((new Attribute)->getHidden())->toContain('tenant_id', 'active_name_guard')
        ->and((new AttributeValue)->getHidden())->toContain('tenant_id', 'attributable_type', 'attributable_id');
});

it('defines policy permissions for the attribute resource', function (): void {
    $permissions = array_column(AttributePolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(11);
});

it('defines policy permissions for the attribute value resource', function (): void {
    $permissions = array_column(AttributeValuePolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(11);
});

it('uses kebab-case permission names scoped per model', function (): void {
    $attributePermissions = array_column(AttributePolicyEnum::cases(), 'value');
    $valuePermissions = array_column(AttributeValuePolicyEnum::cases(), 'value');

    expect($attributePermissions)->toHaveCount(count(array_unique($attributePermissions)))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');

    expect($valuePermissions)->toHaveCount(count(array_unique($valuePermissions)))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');
});

it('logs activity for attribute models', function (): void {
    expect(new Attribute)->toBeInstanceOf(ShouldLogActivity::class)
        ->and(new AttributeValue)->toBeInstanceOf(ShouldLogActivity::class);
});
