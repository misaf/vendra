<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\SoftDeletes;
use Misaf\VendraFaq\Enums\FaqCategoryPolicyEnum;
use Misaf\VendraFaq\Enums\FaqPolicyEnum;
use Misaf\VendraFaq\Models\Faq;
use Misaf\VendraFaq\Models\FaqCategory;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

it('applies shared tenant ownership and soft deletes to faq models', function (): void {
    expect(class_uses_recursive(Faq::class))->toContain(BelongsToTenant::class, SoftDeletes::class)
        ->and(class_uses_recursive(FaqCategory::class))->toContain(BelongsToTenant::class, SoftDeletes::class);
});

it('defines translatable fields on faq models', function (): void {
    expect((new Faq)->translatable)->toBe(['name', 'description', 'slug'])
        ->and((new FaqCategory)->translatable)->toBe(['name', 'description', 'slug']);
});

it('hides the tenant association from faq serialization', function (): void {
    expect((new Faq)->getHidden())->toContain('tenant_id')
        ->and((new FaqCategory)->getHidden())->toContain('tenant_id');
});

it('defines policy permissions for the faq resource', function (): void {
    $permissions = array_column(FaqPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(11);
});

it('defines policy permissions for the faq category resource', function (): void {
    $permissions = array_column(FaqCategoryPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(11);
});

it('uses kebab-case permission names scoped per model', function (): void {
    $faqPermissions = array_column(FaqPolicyEnum::cases(), 'value');
    $categoryPermissions = array_column(FaqCategoryPolicyEnum::cases(), 'value');

    expect($faqPermissions)->toHaveSameSize(array_unique($faqPermissions))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/')
        ->and($categoryPermissions)->toHaveSameSize(array_unique($categoryPermissions))->each->toMatch('/^[a-z]+(-[a-z]+)*$/');
});
