<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Misaf\VendraCustomPage\Database\Seeders\DemoContentSeeder;
use Misaf\VendraCustomPage\Models\CustomPage;
use Misaf\VendraCustomPage\Models\CustomPageCategory;

it('seeds its demo fixtures again without duplicating rows', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    makeCurrentTestTenant();

    Artisan::call('db:seed', ['--class' => DemoContentSeeder::class, '--force' => true]);

    $customPageCategories = CustomPageCategory::query()->count();
    $customPages = CustomPage::query()->count();

    expect($customPageCategories)->toBeGreaterThan(0)
        ->and($customPages)->toBeGreaterThan(0);

    Artisan::call('db:seed', ['--class' => DemoContentSeeder::class, '--force' => true]);

    expect(CustomPageCategory::query()->count())->toBe($customPageCategories)
        ->and(CustomPage::query()->count())->toBe($customPages);
});
