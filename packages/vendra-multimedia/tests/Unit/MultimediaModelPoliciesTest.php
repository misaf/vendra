<?php

declare(strict_types=1);

use Misaf\VendraMultimedia\Enums\MultimediaPolicyEnum;
use Misaf\VendraMultimedia\Models\Multimedia;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

it('applies shared tenant ownership to the multimedia model', function (): void {
    expect(class_uses_recursive(Multimedia::class))->toContain(BelongsToTenant::class);
});

it('hides the tenant association from multimedia serialization', function (): void {
    expect((new Multimedia)->getHidden())->toContain('tenant_id');
});

it('defines policy permissions for the multimedia resource', function (): void {
    $permissions = array_column(MultimediaPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveCount(10);
});

it('uses kebab-case permission names scoped per model', function (): void {
    $permissions = array_column(MultimediaPolicyEnum::cases(), 'value');

    expect($permissions)->toHaveSameSize(array_unique($permissions))
        ->each->toMatch('/^[a-z]+(-[a-z]+)*$/');
});
