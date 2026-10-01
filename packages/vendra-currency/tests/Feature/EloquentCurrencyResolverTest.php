<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraCurrency\Support\EloquentCurrencyResolver;
use Misaf\VendraSupport\Contracts\CurrencyResolver;

it('provides active currency values from an eloquent model', function (): void {
    Schema::create('support_test_currencies', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('code');
        $table->boolean('is_default')
            ->default(false);
        $table->unsignedBigInteger('position')
            ->default(0);
        $table->boolean('active')
            ->default(false);
    });

    CurrencyResolverTestCurrency::query()->insert([
        [
            'name' => 'US Dollar',
            'code' => 'USD',
            'is_default' => true,
            'position' => 1,
            'active' => true,
        ],
        [
            'name' => 'Euro',
            'code' => 'EUR',
            'is_default' => false,
            'position' => 2,
            'active' => true,
        ],
        [
            'name' => 'British Pound',
            'code' => 'GBP',
            'is_default' => false,
            'position' => 3,
            'active' => false,
        ],
    ]);

    $resolver = new EloquentCurrencyResolver(CurrencyResolverTestCurrency::class);

    expect($resolver->available())->toBeTrue()
        ->and($resolver->defaultCode())->toBe('USD')
        ->and($resolver->options())->toBe([
            'EUR' => 'Euro',
            'USD' => 'US Dollar',
        ])
        ->and($resolver->activeCodes())->toBe(['USD', 'EUR']);
});

it('uses the injected currency fallback when currency values are unavailable', function (bool $tableExists): void {
    if ($tableExists) {
        Schema::create('support_test_currencies', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->boolean('active');
            $table->boolean('is_default');
            $table->unsignedBigInteger('position');
        });
    }

    $fallback = $this->mock(CurrencyResolver::class);
    $fallback->shouldReceive('defaultCode')->once()->andReturn('EUR');
    $fallback->shouldReceive('options')->once()->andReturn(['EUR' => 'Euro']);
    $fallback->shouldReceive('activeCodes')->once()->andReturn(['EUR']);

    $resolver = new EloquentCurrencyResolver(CurrencyResolverTestCurrency::class, fallback: $fallback);

    expect($resolver->defaultCode())->toBe('EUR')
        ->and($resolver->options())->toBe(['EUR' => 'Euro'])
        ->and($resolver->activeCodes())->toBe(['EUR']);
})->with(['empty table' => true, 'missing table' => false]);
