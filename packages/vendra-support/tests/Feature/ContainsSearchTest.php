<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraSupport\Support\ContainsSearch;

beforeEach(function (): void {
    Schema::create('support_test_active_records', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('slug');
        $table->boolean('active');
        $table->timestamps();
    });

    DB::table('support_test_active_records')->insert([
        ['name' => '50% off', 'slug' => 'sale', 'active' => true],
        ['name' => '500 items', 'slug' => 'bulk', 'active' => true],
        ['name' => 'Summer', 'slug' => 'summer_sale', 'active' => true],
        ['name' => 'Winter', 'slug' => 'summerxsale', 'active' => true],
    ]);
});

it('matches the term in any column regardless of case', function (): void {
    $names = ContainsSearch::apply(SupportTestActiveRecord::query(), ['name', 'slug'], 'SALE')->pluck('name');

    expect($names->all())->toEqualCanonicalizing(['50% off', 'Summer', 'Winter']);
});

it('treats wildcards in the term literally', function (string $term, string $expectedName): void {
    $names = ContainsSearch::apply(SupportTestActiveRecord::query(), ['name', 'slug'], $term)->pluck('name');

    expect($names->all())->toBe([$expectedName]);
})->with([
    'percent' => ['0%', '50% off'],
    'underscore' => ['r_s', 'Summer'],
]);
