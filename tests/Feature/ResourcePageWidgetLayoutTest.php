<?php

declare(strict_types=1);

use Filament\Tables\Table;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\AffiliateCommissionResource;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Pages\ListAffiliateCommissions;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\Widgets\AffiliateCommissionOverviewWidget;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliatePayouts\AffiliatePayoutResource;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliatePayouts\Pages\ListAffiliatePayouts;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliatePayouts\Widgets\AffiliatePayoutOverviewWidget;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\AffiliateResource;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Pages\ListAffiliates;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Widgets\AffiliateClicksOverview;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Widgets\AffiliateCommissionsOverview;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\Widgets\AffiliateReferralsOverview;
use Misaf\VendraAuthifyLog\Filament\Clusters\Resources\Widgets\LatestAuthifyLogTableWidget;
use Misaf\VendraCart\Filament\Clusters\Resources\Carts\CartResource;
use Misaf\VendraCart\Filament\Clusters\Resources\Carts\Pages\ListCarts;
use Misaf\VendraCart\Filament\Clusters\Resources\Carts\Widgets\CartOverviewWidget;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\NewsletterResource;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Pages\ListNewsletters;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Widgets\NewsletterOverviewWidget;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\Pages\ListNewsletterSubscribers;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\Widgets\NewsletterSubscriberOverviewWidget;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\ListProducts;
use Misaf\VendraProduct\Filament\Widgets\ProductOverviewWidget;
use Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Widgets\TransactionGatewayChart;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Pages\ListTransactions;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\TransactionBonusOverviewWidget;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\TransactionCommissionOverviewWidget;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\TransactionDepositOverviewWidget;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\TransactionLimitOverviewWidget;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\TransactionTransferOverviewWidget;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\TransactionWithdrawalOverviewWidget;
use Misaf\VendraUser\Filament\Clusters\Resources\Users\Pages\ListUsers;
use Misaf\VendraUser\Filament\Clusters\Resources\Users\UserResource;
use Misaf\VendraUser\Filament\Clusters\Resources\Users\Widgets\UserOverviewWidget;

it('uses the same responsive widget grid on every resource page', function (string $pageClass): void {
    expect(resolve($pageClass)->getHeaderWidgetsColumns())->toBe([
        'sm' => 1,
        'md' => 2,
        'lg' => 3,
    ]);
})->with([
    ListProducts::class,
    ListAffiliates::class,
    ListTransactions::class,
    ListCarts::class,
    ListUsers::class,
    ListNewsletters::class,
    ListNewsletterSubscribers::class,
    ListAffiliateCommissions::class,
    ListAffiliatePayouts::class,
]);

it('moves resource header widgets after content on mobile', function (): void {
    $headerWidgets = resolve(ListTransactions::class)->getSchema('headerWidgets');

    expect($headerWidgets)->not->toBeNull()
        ->and($headerWidgets->getExtraAttributeBag()->get('class'))->toBe('max-md:order-last');
});

it('registers each combined overview on its resource page', function (string $pageClass, string $widgetClass): void {
    $headerWidgets = new ReflectionMethod($pageClass, 'getHeaderWidgets')
        ->invoke(resolve($pageClass));

    expect($headerWidgets)->toContain($widgetClass);
})->with([
    [ListCarts::class, CartOverviewWidget::class],
    [ListUsers::class, UserOverviewWidget::class],
    [ListNewsletters::class, NewsletterOverviewWidget::class],
    [ListNewsletterSubscribers::class, NewsletterSubscriberOverviewWidget::class],
    [ListAffiliateCommissions::class, AffiliateCommissionOverviewWidget::class],
    [ListAffiliatePayouts::class, AffiliatePayoutOverviewWidget::class],
]);

it('registers each overview with its resource', function (string $resourceClass, string $widgetClass): void {
    expect($resourceClass::getWidgets())->toContain($widgetClass);
})->with([
    [CartResource::class, CartOverviewWidget::class],
    [UserResource::class, UserOverviewWidget::class],
    [NewsletterResource::class, NewsletterOverviewWidget::class],
    [NewsletterSubscriberResource::class, NewsletterSubscriberOverviewWidget::class],
    [AffiliateCommissionResource::class, AffiliateCommissionOverviewWidget::class],
    [AffiliatePayoutResource::class, AffiliatePayoutOverviewWidget::class],
]);

it('registers the individual affiliate overviews with their resource', function (): void {
    expect(AffiliateResource::getWidgets())->toBe([
        AffiliateClicksOverview::class,
        AffiliateReferralsOverview::class,
        AffiliateCommissionsOverview::class,
    ]);
});

it('uses equal spans for individual resource stat widgets', function (string $widgetClass): void {
    expect(resolve($widgetClass)->getColumnSpan())->toBe(['sm' => 1]);
})->with([
    AffiliateClicksOverview::class,
    AffiliateReferralsOverview::class,
    AffiliateCommissionsOverview::class,
    TransactionDepositOverviewWidget::class,
    TransactionWithdrawalOverviewWidget::class,
    TransactionCommissionOverviewWidget::class,
    TransactionBonusOverviewWidget::class,
    TransactionTransferOverviewWidget::class,
    TransactionLimitOverviewWidget::class,
]);

it('disables polling on every resource stat widget', function (string $widgetClass): void {
    $pollingInterval = new ReflectionMethod($widgetClass, 'getPollingInterval')
        ->invoke(resolve($widgetClass));

    expect($pollingInterval)->toBeNull();
})->with([
    ProductOverviewWidget::class,
    CartOverviewWidget::class,
    UserOverviewWidget::class,
    NewsletterOverviewWidget::class,
    NewsletterSubscriberOverviewWidget::class,
    AffiliateCommissionOverviewWidget::class,
    AffiliatePayoutOverviewWidget::class,
    AffiliateClicksOverview::class,
    AffiliateReferralsOverview::class,
    AffiliateCommissionsOverview::class,
    TransactionDepositOverviewWidget::class,
    TransactionWithdrawalOverviewWidget::class,
    TransactionCommissionOverviewWidget::class,
    TransactionBonusOverviewWidget::class,
    TransactionTransferOverviewWidget::class,
    TransactionLimitOverviewWidget::class,
    TransactionGatewayChart::class,
]);

it('uses full width for combined and detailed resource widgets', function (string $widgetClass): void {
    expect(resolve($widgetClass)->getColumnSpan())->toBe('full');
})->with([
    ProductOverviewWidget::class,
    CartOverviewWidget::class,
    UserOverviewWidget::class,
    NewsletterOverviewWidget::class,
    NewsletterSubscriberOverviewWidget::class,
    AffiliateCommissionOverviewWidget::class,
    AffiliatePayoutOverviewWidget::class,
    TransactionGatewayChart::class,
    LatestAuthifyLogTableWidget::class,
]);

it('disables polling on resource table widgets', function (): void {
    $widget = resolve(LatestAuthifyLogTableWidget::class);

    expect($widget->table(Table::make($widget))->getPollingInterval())->toBeNull();
});
