<?php

declare(strict_types=1);

use Misaf\VendraCurrency\Enums\CurrencyPolicyEnum;
use Misaf\VendraCurrency\Models\Currency;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

it('applies shared tenant ownership to the currency model', function (): void {
    expect(class_uses_recursive(Currency::class))->toContain(BelongsToTenant::class);
});

it('hides tenant and internal guard attributes from currency serialization', function (): void {
    expect((new Currency)->getHidden())->toContain('tenant_id', 'default_guard');
});

it('defines policy permissions for the currency resource', function (): void {
    $permissions = array_column(CurrencyPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(7);
});

it('uses kebab-case permission names scoped per model', function (): void {
    $permissions = array_column(CurrencyPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveSameSize(array_unique($permissions))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');
});
