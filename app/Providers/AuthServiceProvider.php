<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Blog\BlogPost;
use App\Models\Blog\BlogPostCategory;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyCategory;
use App\Models\Faq\Faq;
use App\Models\Faq\FaqCategory;
use App\Models\Geographical\GeographicalCity;
use App\Models\Geographical\GeographicalCountry;
use App\Models\Geographical\GeographicalNeighborhood;
use App\Models\Geographical\GeographicalState;
use App\Models\Geographical\GeographicalZone;
use App\Models\Language\Language;
use App\Models\Language\LanguageLine;
use App\Models\Order\Order;
use App\Models\Order\OrderProduct;
use App\Models\Permission\Permission;
use App\Models\Permission\Role;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Product\ProductPrice;
use App\Models\Transaction\Transaction;
use App\Models\User;
use App\Models\User\UserProfile;
use App\Models\User\UserProfileBalance;
use App\Models\User\UserProfileDocument;
use App\Models\User\UserProfilePhone;
use App\Policies\Blog\BlogPostCategoryPolicy;
use App\Policies\Blog\BlogPostPolicy;
use App\Policies\Currency\CurrencyCategoryPolicy;
use App\Policies\Currency\CurrencyPolicy;
use App\Policies\Faq\FaqCategoryPolicy;
use App\Policies\Faq\FaqPolicy;
use App\Policies\Geographical\GeographicalCityPolicy;
use App\Policies\Geographical\GeographicalCountryPolicy;
use App\Policies\Geographical\GeographicalNeighborhoodPolicy;
use App\Policies\Geographical\GeographicalStatePolicy;
use App\Policies\Geographical\GeographicalZonePolicy;
use App\Policies\Language\LanguageLinePolicy;
use App\Policies\Language\LanguagePolicy;
use App\Policies\Order\OrderPolicy;
use App\Policies\Order\OrderProductPolicy;
use App\Policies\Permission\PermissionPolicy;
use App\Policies\Permission\RolePolicy;
use App\Policies\Product\ProductCategoryPolicy;
use App\Policies\Product\ProductPolicy;
use App\Policies\Product\ProductPricePolicy;
use App\Policies\Transaction\TransactionPolicy;
use App\Policies\User\UserPolicy;
use App\Policies\User\UserProfileBalancePolicy;
use App\Policies\User\UserProfileDocumentPolicy;
use App\Policies\User\UserProfilePhonePolicy;
use App\Policies\User\UserProfilePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

final class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        BlogPost::class                 => BlogPostPolicy::class,
        BlogPostCategory::class         => BlogPostCategoryPolicy::class,
        Currency::class                 => CurrencyPolicy::class,
        CurrencyCategory::class         => CurrencyCategoryPolicy::class,
        Faq::class                      => FaqPolicy::class,
        FaqCategory::class              => FaqCategoryPolicy::class,
        GeographicalCity::class         => GeographicalCityPolicy::class,
        GeographicalCountry::class      => GeographicalCountryPolicy::class,
        GeographicalNeighborhood::class => GeographicalNeighborhoodPolicy::class,
        GeographicalState::class        => GeographicalStatePolicy::class,
        GeographicalZone::class         => GeographicalZonePolicy::class,
        Language::class                 => LanguagePolicy::class,
        LanguageLine::class             => LanguageLinePolicy::class,
        Permission::class               => PermissionPolicy::class,
        Role::class                     => RolePolicy::class,
        Product::class                  => ProductPolicy::class,
        ProductCategory::class          => ProductCategoryPolicy::class,
        ProductPrice::class             => ProductPricePolicy::class,
        User::class                     => UserPolicy::class,
        UserProfile::class              => UserProfilePolicy::class,
        UserProfileBalance::class       => UserProfileBalancePolicy::class,
        UserProfileDocument::class      => UserProfileDocumentPolicy::class,
        UserProfilePhone::class         => UserProfilePhonePolicy::class,
        Order::class                    => OrderPolicy::class,
        OrderProduct::class             => OrderProductPolicy::class,
        Transaction::class              => TransactionPolicy::class,
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }
        });
    }
}
