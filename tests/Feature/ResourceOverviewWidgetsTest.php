<?php

declare(strict_types=1);

use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Misaf\VendraCart\Database\Factories\CartFactory;
use Misaf\VendraCart\Filament\Clusters\Resources\Carts\Widgets\CartOverviewWidget;
use Misaf\VendraNewsletter\Database\Factories\NewsletterFactory;
use Misaf\VendraNewsletter\Database\Factories\NewsletterSubscriberFactory;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Widgets\NewsletterOverviewWidget;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\Widgets\NewsletterSubscriberOverviewWidget;
use Misaf\VendraStore\Database\Factories\StoreFactory;
use Misaf\VendraUser\Database\Factories\UserFactory;
use Misaf\VendraUser\Filament\Clusters\Resources\Users\Widgets\UserOverviewWidget;

it('shows tenant-scoped cart user and newsletter metrics', function (): void {
    $locale = app()->getLocale();
    app()->setLocale('en');

    try {
        $tenant = StoreFactory::new()->active()->createOne();
        $tenant->makeCurrent();

        CartFactory::new()->count(2)->create();
        CartFactory::new()->create(['expires_at' => now()->subDay()]);

        UserFactory::new()->count(2)->create();
        UserFactory::new()->unverified()->create();

        NewsletterFactory::new()->draft()->create();
        NewsletterFactory::new()->scheduled()->count(2)->create();
        NewsletterFactory::new()->sent()->count(3)->create();

        NewsletterSubscriberFactory::new()->subscribed()->count(2)->create();
        NewsletterSubscriberFactory::new()->unsubscribed()->create();

        $otherTenant = StoreFactory::new()->active()->createOne();
        $otherTenant->makeCurrent();

        CartFactory::new()->create();
        UserFactory::new()->create();
        NewsletterFactory::new()->sent()->create();
        NewsletterSubscriberFactory::new()->subscribed()->create();

        $tenant->makeCurrent();

        $statsFor = static function (string $widgetClass): array {
            /** @var array<int, Stat> $stats */
            $stats = new ReflectionMethod($widgetClass, 'getStats')
                ->invoke(resolve($widgetClass));

            return $stats;
        };

        $valuesFor = static fn (string $widgetClass): array => array_map(
            static fn (Stat $stat): mixed => $stat->getValue(),
            $statsFor($widgetClass),
        );

        expect($valuesFor(CartOverviewWidget::class))->toBe(['3', '2', '1'])
            ->and($valuesFor(UserOverviewWidget::class))->toBe(['3', '2', '1'])
            ->and($valuesFor(NewsletterOverviewWidget::class))->toBe(['1', '2', '3'])
            ->and($valuesFor(NewsletterSubscriberOverviewWidget::class))->toBe(['3', '2', '1']);

        foreach ([
            CartOverviewWidget::class => Heroicon::OutlinedShoppingCart,
            UserOverviewWidget::class => Heroicon::OutlinedUsers,
            NewsletterOverviewWidget::class => Heroicon::OutlinedEnvelope,
            NewsletterSubscriberOverviewWidget::class => Heroicon::OutlinedEnvelope,
        ] as $widgetClass => $icon) {
            $stats = $statsFor($widgetClass);

            expect(array_map(
                static fn (Stat $stat): mixed => $stat->getIcon(),
                $stats,
            ))->each->toBe($icon)
                ->and(array_map(
                    static fn (Stat $stat): IconPosition => $stat->getDescriptionIconPosition(),
                    $stats,
                ))->each->toBe(IconPosition::Before)
                ->and(array_map(
                    static fn (Stat $stat): int => count($stat->getChart() ?? []),
                    $stats,
                ))->each->toBeGreaterThan(0);
        }
    } finally {
        app()->setLocale($locale);
    }
});
