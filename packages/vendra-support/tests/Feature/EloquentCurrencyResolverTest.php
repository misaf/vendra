<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraSupport\Capabilities\EloquentCurrencyResolver;

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

    SupportTestCurrency::query()->insert([
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

    $resolver = new EloquentCurrencyResolver(SupportTestCurrency::class);

    expect($resolver->available())->toBeTrue()
        ->and($resolver->defaultCode())->toBe('USD')
        ->and($resolver->options())->toBe([
            'EUR' => 'Euro',
            'USD' => 'US Dollar',
        ])
        ->and($resolver->activeCodes())->toBe(['USD', 'EUR']);
});
