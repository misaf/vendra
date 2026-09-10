<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\SoftDeletes;
use Misaf\VendraCustomPage\Enums\CustomPageCategoryPolicyEnum;
use Misaf\VendraCustomPage\Enums\CustomPagePolicyEnum;
use Misaf\VendraCustomPage\Models\CustomPage;
use Misaf\VendraCustomPage\Models\CustomPageCategory;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

it('applies shared tenant ownership and soft deletes to custom page models', function (): void {
    expect(class_uses_recursive(CustomPage::class))->toContain(BelongsToTenant::class, SoftDeletes::class)
        ->and(class_uses_recursive(CustomPageCategory::class))->toContain(BelongsToTenant::class, SoftDeletes::class);
});

it('defines translatable fields on custom page models', function (): void {
    expect((new CustomPage)->translatable)->toBe(['name', 'description', 'slug'])
        ->and((new CustomPageCategory)->translatable)->toBe(['name', 'description', 'slug']);
});

it('hides the tenant association from custom page serialization', function (): void {
    expect((new CustomPage)->getHidden())->toContain('tenant_id')
        ->and((new CustomPageCategory)->getHidden())->toContain('tenant_id');
});

it('defines policy permissions for the custom page resource', function (): void {
    $permissions = array_column(CustomPagePolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(11);
});

it('defines policy permissions for the custom page category resource', function (): void {
    $permissions = array_column(CustomPageCategoryPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(11);
});

it('uses kebab-case permission names scoped per model', function (): void {
    $pagePermissions = array_column(CustomPagePolicyEnum::cases(), 'value');
    $categoryPermissions = array_column(CustomPageCategoryPolicyEnum::cases(), 'value');

    expect($pagePermissions)->toHaveSameSize(array_unique($pagePermissions))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/')
        ->and($categoryPermissions)->toHaveSameSize(array_unique($categoryPermissions))->each->toMatch('/^[a-z]+(-[a-z]+)*$/');
});
