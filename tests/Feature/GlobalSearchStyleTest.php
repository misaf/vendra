<?php

declare(strict_types=1);

use Filament\Resources\Resource;
use Misaf\VendraActivityLog\Filament\Clusters\Resources\ActivityLogResource;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliateCommissions\AffiliateCommissionResource;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\AffiliatePayouts\AffiliatePayoutResource;
use Misaf\VendraAffiliate\Filament\Clusters\Resources\Affiliates\AffiliateResource;
use Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\AttributeResource;
use Misaf\VendraAuthifyLog\Filament\Clusters\Resources\AuthifyLogResource;
use Misaf\VendraBlog\Filament\Clusters\Resources\BlogPostCategories\BlogPostCategoryResource;
use Misaf\VendraBlog\Filament\Clusters\Resources\BlogPosts\BlogPostResource;
use Misaf\VendraCart\Filament\Clusters\Resources\Carts\CartResource;
use Misaf\VendraConsole\Filament\Resources\Plans\PlanResource;
use Misaf\VendraConsole\Filament\Resources\Resellers\ResellerResource;
use Misaf\VendraConsole\Filament\Resources\Stores\StoreResource;
use Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\CurrencyResource;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPageCategories\CustomPageCategoryResource;
use Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\CustomPageResource;
use Misaf\VendraFaq\Filament\Clusters\Resources\FaqCategories\FaqCategoryResource;
use Misaf\VendraFaq\Filament\Clusters\Resources\Faqs\FaqResource;
use Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines\LanguageLineResource;
use Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\LanguageResource;
use Misaf\VendraMultimedia\Filament\Clusters\Resources\MultimediaResource;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\NewsletterResource;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\PermissionResource;
use Misaf\VendraPermission\Filament\Clusters\Resources\Roles\RoleResource;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\ProductCategoryResource;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductPrices\ProductPriceResource;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\ProductResource;
use Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\TaggerResource;
use Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\TransactionGatewayResource;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\TransactionResource;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\WalletResource;
use Misaf\VendraUser\Filament\Clusters\Resources\Users\UserResource;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\UserProfileResource;

beforeEach(function (): void {
    app()->setLocale('en');
});

it('configures every host and package resource with the normalized global search style', function (string $resource, bool $hasCustomResultUrl): void {
    expect(is_subclass_of($resource, Resource::class))->toBeTrue();

    $attributes = $resource::getGloballySearchableAttributes();
    $descriptionAttributes = array_filter(
        $attributes,
        static fn (string $attribute): bool => str_contains($attribute, 'description'),
    );
    $declaresCustomResultUrl = new ReflectionMethod($resource, 'getGlobalSearchResultUrl')
        ->getDeclaringClass()
        ->getName() === $resource;

    expect($attributes)
        ->not->toBeEmpty()
        ->and($descriptionAttributes)->toBeEmpty()
        ->and(
            $resource::hasPage('view')
            || $resource::hasPage('edit')
            || ($hasCustomResultUrl && $declaresCustomResultUrl),
        )
        ->toBeTrue();
})->with('searchable resources');

it('keeps the page-less product price resource out of global search', function (): void {
    $isGloballySearchable = new ReflectionProperty(ProductPriceResource::class, 'isGloballySearchable');

    expect($isGloballySearchable->getValue())->toBeFalse()
        ->and(ProductPriceResource::getGloballySearchableAttributes())->toBeEmpty()
        ->and(ProductPriceResource::hasPage('view'))->toBeFalse()
        ->and(ProductPriceResource::hasPage('edit'))->toBeFalse();
});

dataset('searchable resources', [
    'console plans' => [PlanResource::class, false],
    'console properties' => [StoreResource::class, false],
    'console resellers' => [ResellerResource::class, false],
    'reseller properties' => [Misaf\VendraReseller\Filament\Resources\Stores\StoreResource::class, true],
    'activity logs' => [ActivityLogResource::class, false],
    'affiliate commissions' => [AffiliateCommissionResource::class, false],
    'affiliate payouts' => [AffiliatePayoutResource::class, false],
    'affiliates' => [AffiliateResource::class, false],
    'attributes' => [AttributeResource::class, false],
    'authentication logs' => [AuthifyLogResource::class, false],
    'blog post categories' => [BlogPostCategoryResource::class, false],
    'blog posts' => [BlogPostResource::class, false],
    'carts' => [CartResource::class, false],
    'currencies' => [CurrencyResource::class, false],
    'custom page categories' => [CustomPageCategoryResource::class, false],
    'custom pages' => [CustomPageResource::class, false],
    'faq categories' => [FaqCategoryResource::class, false],
    'faqs' => [FaqResource::class, false],
    'language lines' => [LanguageLineResource::class, false],
    'languages' => [LanguageResource::class, false],
    'multimedia' => [MultimediaResource::class, false],
    'newsletter subscribers' => [NewsletterSubscriberResource::class, false],
    'newsletters' => [NewsletterResource::class, false],
    'permissions' => [PermissionResource::class, false],
    'roles' => [RoleResource::class, false],
    'product categories' => [ProductCategoryResource::class, false],
    'products' => [ProductResource::class, false],
    'tags' => [TaggerResource::class, false],
    'transaction gateways' => [TransactionGatewayResource::class, false],
    'transactions' => [TransactionResource::class, false],
    'wallets' => [WalletResource::class, false],
    'user profiles' => [UserProfileResource::class, false],
    'users' => [UserResource::class, false],
]);
