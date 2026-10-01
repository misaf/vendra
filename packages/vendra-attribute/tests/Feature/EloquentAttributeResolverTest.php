<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraAttribute\Support\EloquentAttributeResolver;
use Misaf\VendraSupport\Contracts\AttributeResolver;

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
