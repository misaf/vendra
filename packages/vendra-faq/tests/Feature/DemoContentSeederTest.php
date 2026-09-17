<?php

declare(strict_types=1);

use Misaf\VendraFaq\Database\Seeders\DemoContentSeeder;
use Misaf\VendraFaq\Models\Faq;
use Misaf\VendraFaq\Models\FaqCategory;

it('seeds its demo fixtures again without duplicating rows', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    makeCurrentTestTenant();

    resolve(DemoContentSeeder::class)->run();

    $faqCategories = FaqCategory::query()->count();
    $faqs = Faq::query()->count();

    expect($faqCategories)->toBeGreaterThan(0)
        ->and($faqs)->toBeGreaterThan(0);

    resolve(DemoContentSeeder::class)->run();

    expect(FaqCategory::query()->count())->toBe($faqCategories)
        ->and(Faq::query()->count())->toBe($faqs);
});
