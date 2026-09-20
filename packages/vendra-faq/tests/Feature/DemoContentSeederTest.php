<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Misaf\VendraFaq\Database\Seeders\DemoContentSeeder;
use Misaf\VendraFaq\Models\Faq;
use Misaf\VendraFaq\Models\FaqCategory;

it('seeds its demo fixtures again without duplicating rows', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    makeCurrentTestTenant();

    Artisan::call('db:seed', ['--class' => DemoContentSeeder::class, '--force' => true]);

    $faqCategories = FaqCategory::query()->count();
    $faqs = Faq::query()->count();

    expect($faqCategories)->toBeGreaterThan(0)
        ->and($faqs)->toBeGreaterThan(0);

    Artisan::call('db:seed', ['--class' => DemoContentSeeder::class, '--force' => true]);

    expect(FaqCategory::query()->count())->toBe($faqCategories)
        ->and(Faq::query()->count())->toBe($faqs);
});
