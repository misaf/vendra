<?php

declare(strict_types=1);

use App\Filament\Admin\Pages\ManageGeneralSettings;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Misaf\VendraActivityLog\Filament\Clusters\Resources\ActivityLogResource;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\AffiliateCommissionResource;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliatePayouts\AffiliatePayoutResource;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\AffiliateResource;
use Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\AttributeResource;
use Misaf\VendraAuthifyLog\Filament\Clusters\Resources\AuthifyLogResource;
use Misaf\VendraBlog\Filament\Clusters\Resources\BlogPostCategories\BlogPostCategoryResource;
use Misaf\VendraBlog\Filament\Clusters\Resources\BlogPosts\BlogPostResource;
use Misaf\VendraCart\Filament\Clusters\Resources\Carts\CartResource;
use Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\CurrencyResource;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPageCategories\CustomPageCategoryResource;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\CustomPageResource;
use Misaf\VendraDelivery\Filament\Clusters\Resources\Deliveries\DeliveryResource;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\DeliverySlotResource;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliveryZones\DeliveryZoneResource;
use Misaf\VendraFaq\Filament\Clusters\Resources\FaqCategories\FaqCategoryResource;
use Misaf\VendraFaq\Filament\Clusters\Resources\Faqs\FaqResource;
use Misaf\VendraInquiry\Filament\Clusters\Resources\Inquiries\InquiryResource;
use Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines\LanguageLineResource;
use Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\LanguageResource;
use Misaf\VendraMultimedia\Filament\Clusters\Resources\MultimediaResource;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\NewsletterResource;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use Misaf\VendraOrder\Filament\Clusters\Resources\Orders\OrderResource;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\PermissionResource;
use Misaf\VendraPermission\Filament\Clusters\Resources\Roles\RoleResource;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\ProductCategoryResource;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductPrices\ProductPriceResource;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\ProductResource;
use Misaf\VendraSupport\Filament\Clusters\CatalogCluster;
use Misaf\VendraSupport\Filament\Clusters\ContentCluster;
use Misaf\VendraSupport\Filament\Clusters\CustomersCluster;
use Misaf\VendraSupport\Filament\Clusters\LocalizationCluster;
use Misaf\VendraSupport\Filament\Clusters\MarketingCluster;
use Misaf\VendraSupport\Filament\Clusters\SalesCluster;
use Misaf\VendraSupport\Filament\Clusters\SystemCluster;
use Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\TaggerResource;
use Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\TransactionGatewayResource;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\TransactionResource;
use Misaf\VendraUser\Filament\Clusters\Resources\Users\UserResource;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\UserProfileResource;
use Misaf\VendraWishlist\Filament\Clusters\Resources\Wishlists\WishlistResource;

it('uses unique domain slugs for cluster routes and active navigation', function (string $cluster, string $slug): void {
    expect($cluster::getSlug())->toBe($slug)
        ->and($cluster::getNavigationItemActiveRoutePattern())->toContain(".{$slug}.");
})->with([
    'catalog' => [CatalogCluster::class, 'catalog'],
    'sales' => [SalesCluster::class, 'sales'],
    'customers' => [CustomersCluster::class, 'customers'],
    'content' => [ContentCluster::class, 'content'],
    'marketing' => [MarketingCluster::class, 'marketing'],
    'localization' => [LocalizationCluster::class, 'localization'],
    'system' => [SystemCluster::class, 'system'],
]);

it('activates only the owning domain navigation item', function (string $routeName, string $expectedCluster): void {
    Filament::setCurrentPanel('admin');

    try {
        $activeClusters = collect([
            CatalogCluster::class,
            SalesCluster::class,
            CustomersCluster::class,
            ContentCluster::class,
            MarketingCluster::class,
            LocalizationCluster::class,
            SystemCluster::class,
        ])->filter(
            static fn (string $cluster): bool => Str::is(
                $cluster::getNavigationItemActiveRoutePattern(),
                $routeName,
            ),
        )->values()->all();

        expect($activeClusters)->toBe([$expectedCluster]);
    } finally {
        Filament::setCurrentPanel(null);
    }
})->with([
    'catalog' => ['filament.admin.catalog.resources.products.index', CatalogCluster::class],
    'sales' => ['filament.admin.sales.resources.transactions.index', SalesCluster::class],
    'customers' => ['filament.admin.customers.resources.users.index', CustomersCluster::class],
    'content' => ['filament.admin.content.resources.blog-posts.index', ContentCluster::class],
    'marketing' => ['filament.admin.marketing.resources.affiliates.index', MarketingCluster::class],
    'localization' => ['filament.admin.localization.resources.languages.index', LocalizationCluster::class],
    'system' => ['filament.admin.system.pages.configurations', SystemCluster::class],
]);

it('uses the full resource name as the resource slug', function (string $resource): void {
    $expectedSlug = Str::of(class_basename($resource))
        ->beforeLast('Resource')
        ->pluralStudly()
        ->kebab()
        ->toString();

    expect($resource::getSlug())->toBe($expectedSlug);
})->with([
    AffiliateCommissionResource::class,
    AffiliatePayoutResource::class,
    AffiliateResource::class,
    ActivityLogResource::class,
    AttributeResource::class,
    AuthifyLogResource::class,
    BlogPostCategoryResource::class,
    BlogPostResource::class,
    CartResource::class,
    CurrencyResource::class,
    DeliveryResource::class,
    DeliverySlotResource::class,
    DeliveryZoneResource::class,
    CustomPageCategoryResource::class,
    CustomPageResource::class,
    FaqCategoryResource::class,
    FaqResource::class,
    InquiryResource::class,
    LanguageLineResource::class,
    LanguageResource::class,
    MultimediaResource::class,
    NewsletterResource::class,
    NewsletterSubscriberResource::class,
    OrderResource::class,
    PermissionResource::class,
    ProductCategoryResource::class,
    ProductPriceResource::class,
    ProductResource::class,
    RoleResource::class,
    TaggerResource::class,
    TransactionGatewayResource::class,
    TransactionResource::class,
    UserProfileResource::class,
    UserResource::class,
    WishlistResource::class,
]);

it('assigns each admin resource to its domain cluster', function (string $resource, string $cluster): void {
    expect($resource::getCluster())->toBe($cluster)
        ->and($resource::getRouteBaseName(Filament::getPanel('admin')))->toStartWith(
            'filament.admin.'.$cluster::getSlug().'.resources.',
        );
})->with([
    'catalog / products' => [ProductResource::class, CatalogCluster::class],
    'catalog / product categories' => [ProductCategoryResource::class, CatalogCluster::class],
    'catalog / product prices' => [ProductPriceResource::class, CatalogCluster::class],
    'catalog / attributes' => [AttributeResource::class, CatalogCluster::class],
    'sales / carts' => [CartResource::class, SalesCluster::class],
    'sales / orders' => [OrderResource::class, SalesCluster::class],
    'sales / delivery zones' => [DeliveryZoneResource::class, SalesCluster::class],
    'sales / delivery windows' => [DeliverySlotResource::class, SalesCluster::class],
    'sales / deliveries' => [DeliveryResource::class, SalesCluster::class],
    'sales / transactions' => [TransactionResource::class, SalesCluster::class],
    'sales / transaction gateways' => [TransactionGatewayResource::class, SalesCluster::class],
    'sales / currencies' => [CurrencyResource::class, SalesCluster::class],
    'customers / users' => [UserResource::class, CustomersCluster::class],
    'customers / profiles' => [UserProfileResource::class, CustomersCluster::class],
    'customers / roles' => [RoleResource::class, CustomersCluster::class],
    'customers / permissions' => [PermissionResource::class, CustomersCluster::class],
    'customers / wishlists' => [WishlistResource::class, CustomersCluster::class],
    'customers / enquiries' => [InquiryResource::class, CustomersCluster::class],
    'content / blog posts' => [BlogPostResource::class, ContentCluster::class],
    'content / blog post categories' => [BlogPostCategoryResource::class, ContentCluster::class],
    'content / custom pages' => [CustomPageResource::class, ContentCluster::class],
    'content / custom page categories' => [CustomPageCategoryResource::class, ContentCluster::class],
    'content / faqs' => [FaqResource::class, ContentCluster::class],
    'content / faq categories' => [FaqCategoryResource::class, ContentCluster::class],
    'content / multimedia' => [MultimediaResource::class, ContentCluster::class],
    'content / tags' => [TaggerResource::class, ContentCluster::class],
    'marketing / affiliates' => [AffiliateResource::class, MarketingCluster::class],
    'marketing / commissions' => [AffiliateCommissionResource::class, MarketingCluster::class],
    'marketing / payouts' => [AffiliatePayoutResource::class, MarketingCluster::class],
    'marketing / newsletters' => [NewsletterResource::class, MarketingCluster::class],
    'marketing / subscribers' => [NewsletterSubscriberResource::class, MarketingCluster::class],
    'localization / languages' => [LanguageResource::class, LocalizationCluster::class],
    'localization / language lines' => [LanguageLineResource::class, LocalizationCluster::class],
    'system / activity logs' => [ActivityLogResource::class, SystemCluster::class],
    'system / authentication logs' => [AuthifyLogResource::class, SystemCluster::class],
]);

it('assigns general settings to the system domain', function (): void {
    expect(ManageGeneralSettings::getCluster())->toBe(SystemCluster::class)
        ->and(ManageGeneralSettings::getRouteName(Filament::getPanel('admin')))
        ->toStartWith('filament.admin.system.pages.');
});

it('registers domain routes', function (string $routeName): void {
    expect(Route::has($routeName))->toBeTrue();
})->with([
    'catalog products' => 'filament.admin.catalog.resources.products.index',
    'sales transactions' => 'filament.admin.sales.resources.transactions.index',
    'content multimedia' => 'filament.admin.content.resources.multimedia.index',
    'system settings' => 'filament.admin.system.pages.configurations',
]);

it('does not register legacy or user-panel domain routes', function (string $routeName): void {
    expect(Route::has($routeName))->toBeFalse();
})->with([
    'plugin cluster' => 'filament.admin.vendra-product.resources.products.index',
    'standalone cart' => 'filament.admin.resources.carts.index',
    'standalone multimedia' => 'filament.admin.resources.multimedia.index',
    'settings cluster' => 'filament.admin.settings.pages.configurations',
    'user panel domain' => 'filament.user.catalog',
]);
