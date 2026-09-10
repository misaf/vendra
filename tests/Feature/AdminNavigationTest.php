<?php

declare(strict_types=1);

use App\Filament\Admin\Pages\ManageGeneralSettings;
use App\Providers\Filament\AdminPanelServiceProvider;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Lang;
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
use Misaf\VendraSupport\Filament\Navigation\NavigationGroup;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;
use Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\TaggerResource;
use Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\TransactionGatewayResource;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\TransactionResource;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\WalletResource;
use Misaf\VendraUser\Filament\Clusters\Resources\Users\UserResource;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\UserProfileResource;
use Misaf\VendraWishlist\Filament\Clusters\Resources\Wishlists\WishlistResource;

it('uses domain clusters as top-level navigation without redundant groups', function (): void {
    $panel = new AdminPanelServiceProvider(app())->panel(Panel::make());

    expect($panel->getNavigationGroups())->toBeEmpty();
});

it('orders domain clusters predictably', function (string $cluster, int $sort): void {
    expect($cluster::getNavigationSort())->toBe($sort);
})->with([
    'catalog' => [CatalogCluster::class, 1],
    'sales' => [SalesCluster::class, 2],
    'customers' => [CustomersCluster::class, 3],
    'content' => [ContentCluster::class, 4],
    'marketing' => [MarketingCluster::class, 5],
    'localization' => [LocalizationCluster::class, 6],
    'system' => [SystemCluster::class, 7],
]);

it('places carts in the sales cluster', function (): void {
    expect(CartResource::getCluster())->toBe(SalesCluster::class);
});

it('orders navigation resources by centralized priority', function (
    string $resource,
    NavigationPriority $priority,
): void {
    expect($resource::getNavigationSort())->toBe($priority->value);
})->with([
    'products' => [ProductResource::class, NavigationPriority::Products],
    'product categories' => [ProductCategoryResource::class, NavigationPriority::ProductCategories],
    'product prices' => [ProductPriceResource::class, NavigationPriority::ProductPrices],
    'attributes' => [AttributeResource::class, NavigationPriority::Attributes],
    'transactions' => [TransactionResource::class, NavigationPriority::Transactions],
    'transaction gateways' => [TransactionGatewayResource::class, NavigationPriority::TransactionGateways],
    'wallets' => [WalletResource::class, NavigationPriority::Wallets],
    'currencies' => [CurrencyResource::class, NavigationPriority::Currencies],
    'carts' => [CartResource::class, NavigationPriority::Carts],
    'orders' => [OrderResource::class, NavigationPriority::Orders],
    'delivery zones' => [DeliveryZoneResource::class, NavigationPriority::DeliveryZones],
    'delivery windows' => [DeliverySlotResource::class, NavigationPriority::DeliverySlots],
    'deliveries' => [DeliveryResource::class, NavigationPriority::Deliveries],
    'users' => [UserResource::class, NavigationPriority::Users],
    'user profiles' => [UserProfileResource::class, NavigationPriority::UserProfiles],
    'wishlists' => [WishlistResource::class, NavigationPriority::Wishlists],
    'enquiries' => [InquiryResource::class, NavigationPriority::Inquiries],
    'roles' => [RoleResource::class, NavigationPriority::Roles],
    'permissions' => [PermissionResource::class, NavigationPriority::Permissions],
    'blog posts' => [BlogPostResource::class, NavigationPriority::BlogPosts],
    'blog post categories' => [BlogPostCategoryResource::class, NavigationPriority::BlogPostCategories],
    'custom pages' => [CustomPageResource::class, NavigationPriority::CustomPages],
    'custom page categories' => [CustomPageCategoryResource::class, NavigationPriority::CustomPageCategories],
    'faqs' => [FaqResource::class, NavigationPriority::Faqs],
    'faq categories' => [FaqCategoryResource::class, NavigationPriority::FaqCategories],
    'multimedia' => [MultimediaResource::class, NavigationPriority::Multimedia],
    'tags' => [TaggerResource::class, NavigationPriority::Tags],
    'affiliates' => [AffiliateResource::class, NavigationPriority::Affiliates],
    'affiliate commissions' => [AffiliateCommissionResource::class, NavigationPriority::AffiliateCommissions],
    'affiliate payouts' => [AffiliatePayoutResource::class, NavigationPriority::AffiliatePayouts],
    'newsletters' => [NewsletterResource::class, NavigationPriority::Newsletters],
    'newsletter subscribers' => [NewsletterSubscriberResource::class, NavigationPriority::NewsletterSubscribers],
    'languages' => [LanguageResource::class, NavigationPriority::Languages],
    'language lines' => [LanguageLineResource::class, NavigationPriority::LanguageLines],
    'general settings' => [ManageGeneralSettings::class, NavigationPriority::GeneralSettings],
    'activity logs' => [ActivityLogResource::class, NavigationPriority::ActivityLogs],
    'authentication logs' => [AuthifyLogResource::class, NavigationPriority::AuthenticationLogs],
]);

it('assigns every navigation priority a unique sort value', function (): void {
    $values = array_column(NavigationPriority::cases(), 'value');

    expect(array_unique($values))->toHaveSameSize($values);
});

it('uses concise singular and plural resource labels in every configured locale', function (
    string $resource,
    string $singularKey,
    string $pluralKey,
): void {
    $originalLocale = app()->getLocale();

    try {
        foreach (['en', 'de', 'fa'] as $locale) {
            app()->setLocale($locale);

            expect(Lang::has($singularKey, $locale))->toBeTrue()
                ->and(Lang::has($pluralKey, $locale))->toBeTrue()
                ->and($resource::getModelLabel())->toBe(__($singularKey))
                ->and($resource::getNavigationLabel())->toBe(__($pluralKey))
                ->and($resource::getPluralModelLabel())->toBe(__($pluralKey))
                ->and(mb_strlen($resource::getNavigationLabel()))->toBeLessThanOrEqual(24);
        }
    } finally {
        app()->setLocale($originalLocale);
    }
})->with([
    'activity logs' => [ActivityLogResource::class, 'vendra-activity-log::navigation.activity_log', 'vendra-activity-log::navigation.activity_logs'],
    'affiliates' => [AffiliateResource::class, 'vendra-affiliate::navigation.affiliate', 'vendra-affiliate::navigation.affiliates'],
    'affiliate commissions' => [AffiliateCommissionResource::class, 'vendra-affiliate::navigation.affiliate_commission', 'vendra-affiliate::navigation.affiliate_commissions'],
    'affiliate payouts' => [AffiliatePayoutResource::class, 'vendra-affiliate::navigation.affiliate_payout', 'vendra-affiliate::navigation.affiliate_payouts'],
    'attributes' => [AttributeResource::class, 'vendra-attribute::navigation.attribute', 'vendra-attribute::navigation.attributes'],
    'authentication logs' => [AuthifyLogResource::class, 'vendra-authify-log::navigation.authify_log', 'vendra-authify-log::navigation.authify_logs'],
    'blog posts' => [BlogPostResource::class, 'vendra-blog::navigation.blog_post', 'vendra-blog::navigation.blog_posts'],
    'blog categories' => [BlogPostCategoryResource::class, 'vendra-blog::navigation.blog_post_category', 'vendra-blog::navigation.blog_post_categories'],
    'carts' => [CartResource::class, 'vendra-cart::navigation.cart', 'vendra-cart::navigation.carts'],
    'currencies' => [CurrencyResource::class, 'vendra-currency::navigation.currency', 'vendra-currency::navigation.currencies'],
    'custom pages' => [CustomPageResource::class, 'vendra-custom-page::navigation.custom_page', 'vendra-custom-page::navigation.custom_pages'],
    'page categories' => [CustomPageCategoryResource::class, 'vendra-custom-page::navigation.custom_page_category', 'vendra-custom-page::navigation.custom_page_categories'],
    'faqs' => [FaqResource::class, 'vendra-faq::navigation.faq', 'vendra-faq::navigation.faqs'],
    'faq categories' => [FaqCategoryResource::class, 'vendra-faq::navigation.faq_category', 'vendra-faq::navigation.faq_categories'],
    'languages' => [LanguageResource::class, 'vendra-language::navigation.language', 'vendra-language::navigation.languages'],
    'translations' => [LanguageLineResource::class, 'vendra-language::navigation.language_line', 'vendra-language::navigation.language_lines'],
    'media' => [MultimediaResource::class, 'vendra-multimedia::navigation.media_item', 'vendra-multimedia::navigation.media_items'],
    'newsletters' => [NewsletterResource::class, 'vendra-newsletter::navigation.newsletter', 'vendra-newsletter::navigation.newsletters'],
    'subscribers' => [NewsletterSubscriberResource::class, 'vendra-newsletter::navigation.newsletter_subscriber', 'vendra-newsletter::navigation.newsletter_subscribers'],
    'orders' => [OrderResource::class, 'vendra-order::navigation.order', 'vendra-order::navigation.orders'],
    'delivery zones' => [DeliveryZoneResource::class, 'vendra-delivery::navigation.delivery_zone', 'vendra-delivery::navigation.delivery_zones'],
    'delivery windows' => [DeliverySlotResource::class, 'vendra-delivery::navigation.delivery_slot', 'vendra-delivery::navigation.delivery_slots'],
    'deliveries' => [DeliveryResource::class, 'vendra-delivery::navigation.delivery', 'vendra-delivery::navigation.deliveries'],
    'permissions' => [PermissionResource::class, 'vendra-permission::navigation.permission', 'vendra-permission::navigation.permissions'],
    'roles' => [RoleResource::class, 'vendra-permission::navigation.role', 'vendra-permission::navigation.roles'],
    'products' => [ProductResource::class, 'vendra-product::navigation.product', 'vendra-product::navigation.products'],
    'product categories' => [ProductCategoryResource::class, 'vendra-product::navigation.product_category', 'vendra-product::navigation.product_categories'],
    'product prices' => [ProductPriceResource::class, 'vendra-product::navigation.product_price', 'vendra-product::navigation.product_prices'],
    'tags' => [TaggerResource::class, 'vendra-tagger::navigation.tagger', 'vendra-tagger::navigation.taggers'],
    'transactions' => [TransactionResource::class, 'vendra-transaction::navigation.transaction', 'vendra-transaction::navigation.transactions'],
    'payment gateways' => [TransactionGatewayResource::class, 'vendra-transaction::navigation.transaction_gateway', 'vendra-transaction::navigation.transaction_gateways'],
    'users' => [UserResource::class, 'vendra-user::navigation.user', 'vendra-user::navigation.users'],
    'user profiles' => [UserProfileResource::class, 'vendra-user-profile::navigation.user_profile', 'vendra-user-profile::navigation.user_profiles'],
    'wishlists' => [WishlistResource::class, 'vendra-wishlist::navigation.wishlist', 'vendra-wishlist::navigation.wishlists'],
    'enquiries' => [InquiryResource::class, 'vendra-inquiry::navigation.inquiry', 'vendra-inquiry::navigation.inquiries'],
    'wallets' => [WalletResource::class, 'vendra-transaction::navigation.wallet', 'vendra-transaction::navigation.wallets'],
]);

it('uses the domain label and icon for each cluster', function (
    string $cluster,
    NavigationGroup $domain,
    Heroicon $icon,
): void {
    expect($cluster::getNavigationLabel())->toBe($domain->getLabel())
        ->and($cluster::getClusterBreadcrumb())->toBe($domain->getLabel())
        ->and($cluster::getNavigationIcon())->toBe($icon)
        ->and($cluster::getNavigationGroup())->toBeNull();
})->with([
    'catalog' => [CatalogCluster::class, NavigationGroup::Catalog, Heroicon::OutlinedSquares2x2],
    'sales' => [SalesCluster::class, NavigationGroup::Sales, Heroicon::OutlinedBanknotes],
    'customers' => [CustomersCluster::class, NavigationGroup::Customers, Heroicon::OutlinedUsers],
    'content' => [ContentCluster::class, NavigationGroup::Content, Heroicon::OutlinedNewspaper],
    'marketing' => [MarketingCluster::class, NavigationGroup::Marketing, Heroicon::OutlinedMegaphone],
    'localization' => [LocalizationCluster::class, NavigationGroup::Localization, Heroicon::OutlinedLanguage],
    'system' => [SystemCluster::class, NavigationGroup::System, Heroicon::OutlinedCog6Tooth],
]);

it('renders domain resources as top sub-navigation tabs', function (string $cluster): void {
    expect($cluster::getSubNavigationPosition())->toBe(SubNavigationPosition::Top);
})->with([
    CatalogCluster::class,
    SalesCluster::class,
    CustomersCluster::class,
    ContentCluster::class,
    MarketingCluster::class,
    LocalizationCluster::class,
    SystemCluster::class,
]);

it('keeps cluster resources ungrouped so priority controls visible order', function (string $resource): void {
    expect($resource::getNavigationGroup())->toBeNull();
})->with([
    'products' => ProductResource::class,
    'product categories' => ProductCategoryResource::class,
    'product prices' => ProductPriceResource::class,
    'attributes' => AttributeResource::class,
    'transactions' => TransactionResource::class,
    'transaction gateways' => TransactionGatewayResource::class,
    'wallets' => WalletResource::class,
    'currencies' => CurrencyResource::class,
    'carts' => CartResource::class,
    'orders' => OrderResource::class,
    'delivery zones' => DeliveryZoneResource::class,
    'delivery windows' => DeliverySlotResource::class,
    'deliveries' => DeliveryResource::class,
    'users' => UserResource::class,
    'user profiles' => UserProfileResource::class,
    'wishlists' => WishlistResource::class,
    'enquiries' => InquiryResource::class,
    'roles' => RoleResource::class,
    'permissions' => PermissionResource::class,
    'blog posts' => BlogPostResource::class,
    'blog post categories' => BlogPostCategoryResource::class,
    'custom pages' => CustomPageResource::class,
    'custom page categories' => CustomPageCategoryResource::class,
    'faqs' => FaqResource::class,
    'faq categories' => FaqCategoryResource::class,
    'multimedia' => MultimediaResource::class,
    'tags' => TaggerResource::class,
    'affiliates' => AffiliateResource::class,
    'affiliate commissions' => AffiliateCommissionResource::class,
    'affiliate payouts' => AffiliatePayoutResource::class,
    'newsletters' => NewsletterResource::class,
    'newsletter subscribers' => NewsletterSubscriberResource::class,
    'languages' => LanguageResource::class,
    'language lines' => LanguageLineResource::class,
]);

it('uses semantic icons for domain resources', function (string $resource, Heroicon $icon): void {
    expect($resource::getNavigationIcon())->toBe($icon);
})->with([
    'activity logs' => [ActivityLogResource::class, Heroicon::OutlinedClipboardDocumentList],
    'affiliate commissions' => [AffiliateCommissionResource::class, Heroicon::OutlinedReceiptPercent],
    'affiliate payouts' => [AffiliatePayoutResource::class, Heroicon::OutlinedBanknotes],
    'affiliates' => [AffiliateResource::class, Heroicon::OutlinedLink],
    'attributes' => [AttributeResource::class, Heroicon::OutlinedAdjustmentsHorizontal],
    'authify logs' => [AuthifyLogResource::class, Heroicon::OutlinedShieldCheck],
    'blog post categories' => [BlogPostCategoryResource::class, Heroicon::OutlinedFolder],
    'blog posts' => [BlogPostResource::class, Heroicon::OutlinedDocumentText],
    'carts' => [CartResource::class, Heroicon::OutlinedShoppingCart],
    'currencies' => [CurrencyResource::class, Heroicon::OutlinedBanknotes],
    'custom page categories' => [CustomPageCategoryResource::class, Heroicon::OutlinedFolder],
    'custom pages' => [CustomPageResource::class, Heroicon::OutlinedDocument],
    'faq categories' => [FaqCategoryResource::class, Heroicon::OutlinedFolder],
    'faqs' => [FaqResource::class, Heroicon::OutlinedQuestionMarkCircle],
    'language lines' => [LanguageLineResource::class, Heroicon::OutlinedChatBubbleBottomCenterText],
    'languages' => [LanguageResource::class, Heroicon::OutlinedLanguage],
    'multimedia' => [MultimediaResource::class, Heroicon::OutlinedPhoto],
    'newsletter subscribers' => [NewsletterSubscriberResource::class, Heroicon::OutlinedUserGroup],
    'newsletters' => [NewsletterResource::class, Heroicon::OutlinedEnvelope],
    'orders' => [OrderResource::class, Heroicon::OutlinedShoppingBag],
    'delivery zones' => [DeliveryZoneResource::class, Heroicon::OutlinedMapPin],
    'delivery windows' => [DeliverySlotResource::class, Heroicon::OutlinedClock],
    'deliveries' => [DeliveryResource::class, Heroicon::OutlinedTruck],
    'permissions' => [PermissionResource::class, Heroicon::OutlinedKey],
    'product categories' => [ProductCategoryResource::class, Heroicon::OutlinedSquares2x2],
    'product prices' => [ProductPriceResource::class, Heroicon::OutlinedCurrencyDollar],
    'products' => [ProductResource::class, Heroicon::OutlinedCube],
    'roles' => [RoleResource::class, Heroicon::OutlinedShieldCheck],
    'taggers' => [TaggerResource::class, Heroicon::OutlinedHashtag],
    'transaction gateways' => [TransactionGatewayResource::class, Heroicon::OutlinedCreditCard],
    'transactions' => [TransactionResource::class, Heroicon::OutlinedArrowsRightLeft],
    'user profiles' => [UserProfileResource::class, Heroicon::OutlinedIdentification],
    'users' => [UserResource::class, Heroicon::OutlinedUserGroup],
    'wallets' => [WalletResource::class, Heroicon::OutlinedWallet],
    'wishlists' => [WishlistResource::class, Heroicon::OutlinedHeart],
    'enquiries' => [InquiryResource::class, Heroicon::OutlinedInbox],
]);
