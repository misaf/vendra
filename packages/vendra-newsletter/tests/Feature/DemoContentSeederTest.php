<?php

declare(strict_types=1);

use Misaf\VendraNewsletter\Database\Seeders\DemoContentSeeder;
use Misaf\VendraNewsletter\Models\Newsletter;
use Misaf\VendraNewsletter\Models\NewsletterSubscriber;

it('seeds its demo fixtures again without duplicating rows', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    makeCurrentTestTenant();

    resolve(DemoContentSeeder::class)->run();

    $newsletters = Newsletter::query()->count();
    $newsletterSubscribers = NewsletterSubscriber::query()->count();

    expect($newsletters)->toBeGreaterThan(0)
        ->and($newsletterSubscribers)->toBeGreaterThan(0);

    resolve(DemoContentSeeder::class)->run();

    expect(Newsletter::query()->count())->toBe($newsletters)
        ->and(NewsletterSubscriber::query()->count())->toBe($newsletterSubscribers);
});
