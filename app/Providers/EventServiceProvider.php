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
use App\Observers\Blog\BlogPostCategoryObserver;
use App\Observers\Blog\BlogPostObserver;
use App\Observers\Currency\CurrencyCategoryObserver;
use App\Observers\Currency\CurrencyObserver;
use App\Observers\Faq\FaqCategoryObserver;
use App\Observers\Faq\FaqObserver;
use App\Observers\Geographical\GeographicalCityObserver;
use App\Observers\Geographical\GeographicalCountryObserver;
use App\Observers\Geographical\GeographicalNeighborhoodObserver;
use App\Observers\Geographical\GeographicalStateObserver;
use App\Observers\Geographical\GeographicalZoneObserver;
use App\Observers\Language\LanguageLineObserver;
use App\Observers\Language\LanguageObserver;
use App\Observers\Permission\PermissionObserver;
use App\Observers\Permission\RoleObserver;
use App\Observers\Product\ProductCategoryObserver;
use App\Observers\Product\ProductObserver;
use App\Observers\Product\ProductPriceObserver;
use App\Observers\Transaction\TransactionObserver;
use App\Observers\User\UserObserver;
use App\Observers\User\UserProfileBalanceObserver;
use App\Observers\User\UserProfileDocumentObserver;
use App\Observers\User\UserProfileObserver;
use App\Observers\User\UserProfilePhoneObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

final class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    protected $observers = [
        BlogPost::class                 => BlogPostObserver::class,
        BlogPostCategory::class         => BlogPostCategoryObserver::class,
        Currency::class                 => CurrencyObserver::class,
        CurrencyCategory::class         => CurrencyCategoryObserver::class,
        Faq::class                      => FaqObserver::class,
        FaqCategory::class              => FaqCategoryObserver::class,
        GeographicalCity::class         => GeographicalCityObserver::class,
        GeographicalCountry::class      => GeographicalCountryObserver::class,
        GeographicalNeighborhood::class => GeographicalNeighborhoodObserver::class,
        GeographicalState::class        => GeographicalStateObserver::class,
        GeographicalZone::class         => GeographicalZoneObserver::class,
        Language::class                 => LanguageObserver::class,
        LanguageLine::class             => LanguageLineObserver::class,
        Permission::class               => PermissionObserver::class,
        Role::class                     => RoleObserver::class,
        Product::class                  => ProductObserver::class,
        ProductCategory::class          => ProductCategoryObserver::class,
        ProductPrice::class             => ProductPriceObserver::class,
        User::class                     => UserObserver::class,
        UserProfile::class              => UserProfileObserver::class,
        UserProfileBalance::class       => UserProfileBalanceObserver::class,
        UserProfileDocument::class      => UserProfileDocumentObserver::class,
        UserProfilePhone::class         => UserProfilePhoneObserver::class,
        Transaction::class              => TransactionObserver::class,
    ];

    public function shouldDiscoverEvents(): bool
    {
        return true;
    }
}
