<?php

declare(strict_types=1);

use Misaf\VendraCustomPage\Database\Seeders\DemoContentSeeder;
use Misaf\VendraCustomPage\Models\CustomPage;
use Misaf\VendraCustomPage\Models\CustomPageCategory;

it('seeds its demo fixtures again without duplicating rows', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    makeCurrentTestTenant();

    resolve(DemoContentSeeder::class)->run();

    $customPageCategories = CustomPageCategory::query()->count();
    $customPages = CustomPage::query()->count();

    expect($customPageCategories)->toBeGreaterThan(0)
        ->and($customPages)->toBeGreaterThan(0);

    resolve(DemoContentSeeder::class)->run();

    expect(CustomPageCategory::query()->count())->toBe($customPageCategories)
        ->and(CustomPage::query()->count())->toBe($customPages);
});
