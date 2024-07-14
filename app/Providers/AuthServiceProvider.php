<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Blog\BlogPost;
use App\Models\Blog\BlogPostCategory;
use App\Models\Blog\Policies\BlogPostCategoryPolicy;
use App\Models\Blog\Policies\BlogPostPolicy;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyCategory;
use App\Models\Currency\Policies\CurrencyCategoryPolicy;
use App\Models\Currency\Policies\CurrencyPolicy;
use App\Models\Faq\Faq;
use App\Models\Faq\FaqCategory;
use App\Models\Faq\Policies\FaqCategoryPolicy;
use App\Models\Faq\Policies\FaqPolicy;
use App\Models\Geographical\GeographicalCity;
use App\Models\Geographical\GeographicalCountry;
use App\Models\Geographical\GeographicalNeighborhood;
use App\Models\Geographical\GeographicalState;
use App\Models\Geographical\GeographicalZone;
use App\Models\Geographical\Policies\GeographicalCityPolicy;
use App\Models\Geographical\Policies\GeographicalCountryPolicy;
use App\Models\Geographical\Policies\GeographicalNeighborhoodPolicy;
use App\Models\Geographical\Policies\GeographicalStatePolicy;
use App\Models\Geographical\Policies\GeographicalZonePolicy;
use App\Models\Language\Language;
use App\Models\Language\LanguageLine;
use App\Models\Language\Policies\LanguageLinePolicy;
use App\Models\Language\Policies\LanguagePolicy;
use App\Models\Order\Order;
use App\Models\Order\OrderProduct;
use App\Models\Order\Policies\OrderPolicy;
use App\Models\Order\Policies\OrderProductPolicy;
use App\Models\Permission\Permission;
use App\Models\Permission\Policies\PermissionPolicy;
use App\Models\Permission\Policies\RolePolicy;
use App\Models\Permission\Role;
use App\Models\Product\Policies\ProductCategoryPolicy;
use App\Models\Product\Policies\ProductPolicy;
use App\Models\Product\Policies\ProductPricePolicy;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Product\ProductPrice;
use App\Models\Transaction\Policies\TransactionPolicy;
use App\Models\Transaction\Transaction;
use App\Models\User\Policies\UserPolicy;
use App\Models\User\Policies\UserProfileBalancePolicy;
use App\Models\User\Policies\UserProfileDocumentPolicy;
use App\Models\User\Policies\UserProfilePhonePolicy;
use App\Models\User\Policies\UserProfilePolicy;
use App\Models\User\UserProfile;
use App\Models\User\UserProfileBalance;
use App\Models\User\UserProfileDocument;
use App\Models\User\UserProfilePhone;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Termehsoft\User\Models\User;

final class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // BlogPost::class                 => BlogPostPolicy::class,
        // BlogPostCategory::class         => BlogPostCategoryPolicy::class,
        // Currency::class                 => CurrencyPolicy::class,
        // CurrencyCategory::class         => CurrencyCategoryPolicy::class,
        // Faq::class                      => FaqPolicy::class,
        // FaqCategory::class              => FaqCategoryPolicy::class,
        // GeographicalCity::class         => GeographicalCityPolicy::class,
        // GeographicalCountry::class      => GeographicalCountryPolicy::class,
        // GeographicalNeighborhood::class => GeographicalNeighborhoodPolicy::class,
        // GeographicalState::class        => GeographicalStatePolicy::class,
        // GeographicalZone::class         => GeographicalZonePolicy::class,
        // Language::class                 => LanguagePolicy::class,
        // LanguageLine::class             => LanguageLinePolicy::class,
        // Permission::class               => PermissionPolicy::class,
        // Role::class                     => RolePolicy::class,
        // Product::class                  => ProductPolicy::class,
        // ProductCategory::class          => ProductCategoryPolicy::class,
        // ProductPrice::class             => ProductPricePolicy::class,
        // User::class                     => UserPolicy::class,
        // UserProfile::class              => UserProfilePolicy::class,
        // UserProfileBalance::class       => UserProfileBalancePolicy::class,
        // UserProfileDocument::class      => UserProfileDocumentPolicy::class,
        // UserProfilePhone::class         => UserProfilePhonePolicy::class,
        // Order::class                    => OrderPolicy::class,
        // OrderProduct::class             => OrderProductPolicy::class,
        // Transaction::class              => TransactionPolicy::class,
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
