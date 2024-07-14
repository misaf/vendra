<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Blog\BlogPost;
use App\Models\Blog\BlogPostCategory;
use App\Models\Blog\Observers\BlogPostCategoryObserver;
use App\Models\Blog\Observers\BlogPostObserver;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyCategory;
use App\Models\Currency\Observers\CurrencyCategoryObserver;
use App\Models\Currency\Observers\CurrencyObserver;
use App\Models\Geographical\GeographicalCity;
use App\Models\Geographical\GeographicalCountry;
use App\Models\Geographical\GeographicalNeighborhood;
use App\Models\Geographical\GeographicalState;
use App\Models\Geographical\GeographicalZone;
use App\Models\Geographical\Observers\GeographicalCityObserver;
use App\Models\Geographical\Observers\GeographicalCountryObserver;
use App\Models\Geographical\Observers\GeographicalNeighborhoodObserver;
use App\Models\Geographical\Observers\GeographicalStateObserver;
use App\Models\Geographical\Observers\GeographicalZoneObserver;
use App\Models\Language\Language;
use App\Models\Language\LanguageLine;
use App\Models\Language\Observers\LanguageLineObserver;
use App\Models\Language\Observers\LanguageObserver;
use App\Models\Permission\Observers\PermissionObserver;
use App\Models\Permission\Observers\RoleObserver;
use App\Models\Permission\Permission;
use App\Models\Permission\Role;
use App\Models\Product\Observers\ProductCategoryObserver;
use App\Models\Product\Observers\ProductObserver;
use App\Models\Product\Observers\ProductPriceObserver;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Product\ProductPrice;
use App\Models\Transaction\Observers\TransactionObserver;
use App\Models\Transaction\Transaction;
use App\Models\User\Observers\UserObserver;
use App\Models\User\Observers\UserProfileBalanceObserver;
use App\Models\User\Observers\UserProfileDocumentObserver;
use App\Models\User\Observers\UserProfileObserver;
use App\Models\User\Observers\UserProfilePhoneObserver;
use App\Models\User\User;
use App\Models\User\UserProfile;
use App\Models\User\UserProfileBalance;
use App\Models\User\UserProfileDocument;
use App\Models\User\UserProfilePhone;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Termehsoft\Faq\Models\Faq;
use Termehsoft\Faq\Models\FaqCategory;
use Termehsoft\Faq\Observers\FaqCategoryObserver;
use Termehsoft\Faq\Observers\FaqObserver;

final class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    protected $observers = [
        // BlogPost::class                 => BlogPostObserver::class,
        // BlogPostCategory::class         => BlogPostCategoryObserver::class,
        // Currency::class                 => CurrencyObserver::class,
        // CurrencyCategory::class         => CurrencyCategoryObserver::class,
        // Faq::class                      => FaqObserver::class,
        // FaqCategory::class              => FaqCategoryObserver::class,
        // GeographicalCity::class         => GeographicalCityObserver::class,
        // GeographicalCountry::class      => GeographicalCountryObserver::class,
        // GeographicalNeighborhood::class => GeographicalNeighborhoodObserver::class,
        // GeographicalState::class        => GeographicalStateObserver::class,
        // GeographicalZone::class         => GeographicalZoneObserver::class,
        // Language::class                 => LanguageObserver::class,
        // LanguageLine::class             => LanguageLineObserver::class,
        // Permission::class               => PermissionObserver::class,
        // Role::class                     => RoleObserver::class,
        // Product::class                  => ProductObserver::class,
        // ProductCategory::class          => ProductCategoryObserver::class,
        // ProductPrice::class             => ProductPriceObserver::class,
        // User::class                     => UserObserver::class,
        // UserProfile::class              => UserProfileObserver::class,
        // UserProfileBalance::class       => UserProfileBalanceObserver::class,
        // UserProfileDocument::class      => UserProfileDocumentObserver::class,
        // UserProfilePhone::class         => UserProfilePhoneObserver::class,
        // Transaction::class              => TransactionObserver::class,
    ];

    public function shouldDiscoverEvents(): bool
    {
        return true;
    }
}
