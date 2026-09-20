<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Misaf\VendraNewsletter\Database\Seeders\DemoContentSeeder;
use Misaf\VendraNewsletter\Models\Newsletter;
use Misaf\VendraNewsletter\Models\NewsletterSubscriber;

it('seeds its demo fixtures again without duplicating rows', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    makeCurrentTestTenant();

    Artisan::call('db:seed', ['--class' => DemoContentSeeder::class, '--force' => true]);

    $newsletters = Newsletter::query()->count();
    $newsletterSubscribers = NewsletterSubscriber::query()->count();

    expect($newsletters)->toBeGreaterThan(0)
        ->and($newsletterSubscribers)->toBeGreaterThan(0);

    Artisan::call('db:seed', ['--class' => DemoContentSeeder::class, '--force' => true]);

    expect(Newsletter::query()->count())->toBe($newsletters)
        ->and(NewsletterSubscriber::query()->count())->toBe($newsletterSubscribers);
});
