<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraAttribute\Support\EloquentAttributeResolver;
use Misaf\VendraSupport\Contracts\AttributeResolver;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\TenantSchema;

it('provides enabled attribute options and the value model', function (): void {
    Schema::create('support_test_attributes', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('unit')->nullable();
        $table->unsignedBigInteger('position')->default(0);
        $table->boolean('active')->default(false);
    });

    AttributeResolverTestAttribute::query()->insert([
        ['name' => 'Material', 'unit' => null, 'position' => 1, 'active' => true],
        ['name' => 'Weight', 'unit' => 'kg', 'position' => 2, 'active' => true],
        ['name' => 'Warranty', 'unit' => 'month', 'position' => 3, 'active' => false],
    ]);

    $resolver = new EloquentAttributeResolver(
        AttributeResolverTestAttribute::class,
        AttributeResolverTestResolvedAttributeValue::class,
    );

    expect($resolver->available())->toBeTrue()
        ->and($resolver->valueModel())->toBe(AttributeResolverTestResolvedAttributeValue::class)
        ->and($resolver->options())->toBe([
            2 => 'Weight (kg)',
            1 => 'Material',
        ]);
});

it('uses the injected attribute fallback when options are unavailable', function (bool $tableExists): void {
    Exceptions::fake();
    if ($tableExists) {
        Schema::create('support_test_attributes', function (Blueprint $table): void {
            $table->id();
            $table->boolean('active');
            $table->unsignedBigInteger('position');
        });
    }

    $fallback = $this->mock(AttributeResolver::class);
    $fallback->shouldReceive('options')->once()->andReturn([7 => 'Fallback']);

    $resolver = new EloquentAttributeResolver(
        AttributeResolverTestAttribute::class,
        AttributeResolverTestResolvedAttributeValue::class,
        fallback: $fallback,
    );

    expect($resolver->options())->toBe([7 => 'Fallback']);
    Exceptions::assertNothingReported();
})->with(['empty table' => true, 'missing table' => false]);

it('isolates a configured attribute model without a tenant scope', function (?int $tenantId, bool $enabled, array $expected): void {
    Schema::create('support_test_attributes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('workspace_id')->nullable();
        $table->string('name');
        $table->string('unit')->nullable();
        $table->boolean('active');
        $table->unsignedBigInteger('position');
    });
    TenantSchema::forgetTenantColumn('support_test_attributes');
    AttributeResolverTestAttribute::query()->insert([
        ['id' => 1, 'workspace_id' => null, 'name' => 'Platform', 'active' => true, 'position' => 1],
        ['id' => 2, 'workspace_id' => 11, 'name' => 'First tenant', 'active' => true, 'position' => 2],
        ['id' => 3, 'workspace_id' => 22, 'name' => 'Second tenant', 'active' => true, 'position' => 3],
        ['id' => 4, 'workspace_id' => 11, 'name' => 'Inactive', 'active' => false, 'position' => 4],
    ]);
    $tenantResolver = $this->mock(TenantResolver::class);
    $tenantResolver->shouldReceive('available')->andReturn($enabled);
    $tenantResolver->shouldReceive('currentId')->andReturn($tenantId);
    $tenantResolver->shouldReceive('foreignKey')->andReturn('workspace_id');

    $resolver = new EloquentAttributeResolver(
        AttributeResolverTestAttribute::class,
        AttributeResolverTestResolvedAttributeValue::class,
    );

    expect($resolver->options())->toBe($expected);
})->with([
    'platform' => [null, true, [1 => 'Platform']],
    'first tenant' => [11, true, [2 => 'First tenant']],
    'second tenant' => [22, true, [3 => 'Second tenant']],
    'tenancy disabled' => [null, false, [3 => 'Second tenant', 2 => 'First tenant', 1 => 'Platform']],
]);

it('reports invalid attribute queries while returning the fallback', function (): void {
    Exceptions::fake();
    Schema::create('support_test_attributes', function (Blueprint $table): void {
        $table->id();
    });
    $resolver = new EloquentAttributeResolver(
        AttributeResolverTestAttribute::class,
        AttributeResolverTestResolvedAttributeValue::class,
        activeColumn: 'missing.active',
    );

    expect($resolver->options())->toBeEmpty();

    Exceptions::assertReported(QueryException::class);
});
