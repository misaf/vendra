<?php

declare(strict_types=1);

use Misaf\VendraLanguage\Enums\LanguageLinePolicyEnum;
use Misaf\VendraLanguage\Enums\LanguagePolicyEnum;
use Misaf\VendraLanguage\Models\Language;
use Misaf\VendraLanguage\Models\LanguageLine;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

it('applies shared tenant ownership to language models', function (): void {
    expect(class_uses_recursive(Language::class))->toContain(BelongsToTenant::class)
        ->and(class_uses_recursive(LanguageLine::class))->toContain(BelongsToTenant::class);
});

it('hides tenant and internal guard attributes from language serialization', function (): void {
    expect((new Language)->getHidden())->toContain('tenant_id', 'default_guard')
        ->and((new LanguageLine)->getHidden())->toContain('tenant_id', 'namespace_guard');
});

it('defines policy permissions for the language resource', function (): void {
    $permissions = array_column(LanguagePolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(7);
});

it('defines policy permissions for the language line resource', function (): void {
    $permissions = array_column(LanguageLinePolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(6);
});

it('uses kebab-case permission names scoped per model', function (): void {
    $languagePermissions = array_column(LanguagePolicyEnum::cases(), 'value');
    $linePermissions = array_column(LanguageLinePolicyEnum::cases(), 'value');

    expect($languagePermissions)->toHaveCount(count(array_unique($languagePermissions)))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');

    expect($linePermissions)->toHaveCount(count(array_unique($linePermissions)))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');
});
