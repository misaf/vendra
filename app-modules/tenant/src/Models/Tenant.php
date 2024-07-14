<?php

declare(strict_types=1);

namespace Termehsoft\Tenant\Models;

use App\Casts\DateCast;
use App\Traits\ActivityLog;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use Spatie\Tags\Tag;
use Termehsoft\Blog\Models\BlogPost;
use Termehsoft\Blog\Models\BlogPostCategory;
use Termehsoft\Currency\Contracts\HasCurrency as CurrencyInterface;
use Termehsoft\Currency\Models\CurrencyCategory;
use Termehsoft\Currency\Traits\HasCurrency as CurrencyTrait;
use Termehsoft\Faq\Models\Faq;
use Termehsoft\Faq\Models\FaqCategory;
use Termehsoft\Geographical\Models\GeographicalCity;
use Termehsoft\Geographical\Models\GeographicalCountry;
use Termehsoft\Geographical\Models\GeographicalNeighborhood;
use Termehsoft\Geographical\Models\GeographicalState;
use Termehsoft\Geographical\Models\GeographicalZone;
use Termehsoft\Language\Models\Language;
use Termehsoft\Language\Models\LanguageLine;
use Termehsoft\Order\Models\Order;
use Termehsoft\Order\Models\OrderProduct;
use Termehsoft\Page\Models\Page;
use Termehsoft\Page\Models\PageCategory;
use Termehsoft\Permission\Models\Permission;
use Termehsoft\Permission\Models\Role;
use Termehsoft\Product\Models\Product;
use Termehsoft\Product\Models\ProductCategory;
use Termehsoft\Product\Models\ProductPrice;
use Termehsoft\Tenant\Traits\BelongsToTenant;
use Termehsoft\User\Contracts\HasUserProfile as UserProfileInterface;
use Termehsoft\User\Contracts\HasUserProfileBalance as UserProfileBalanceInterface;
use Termehsoft\User\Contracts\HasUserProfileDocument as UserProfileDocumentInterface;
use Termehsoft\User\Contracts\HasUserProfilePhone as UserProfilePhoneInterface;
use Termehsoft\User\Traits\HasUserProfile as UserProfileTrait;
use Termehsoft\User\Traits\HasUserProfileBalance as UserProfileBalanceTrait;
use Termehsoft\User\Traits\HasUserProfileDocument as UserProfileDocumentTrait;
use Termehsoft\User\Traits\HasUserProfilePhone as UserProfilePhoneTrait;

class Tenant extends SpatieTenant implements
    CurrencyInterface,
    UserProfileBalanceInterface,
    UserProfileDocumentInterface,
    UserProfileInterface,
    UserProfilePhoneInterface
{
    use ActivityLog;
    use BelongsToTenant;
    use CurrencyTrait;
    use HasSlugOptionsTrait;
    use LogsActivity;
    use UserProfileBalanceTrait;
    use UserProfileDocumentTrait;
    use UserProfilePhoneTrait;
    use UserProfileTrait;

    protected $casts = [
        'id'          => 'integer',
        'name'        => 'string',
        'description' => 'string',
        'slug'        => 'string',
        'status'      => 'boolean',
        'created_at'  => DateCast::class,
        'updated_at'  => DateCast::class,
        'deleted_at'  => DateCast::class,
    ];

    protected $fillable = [
        'name',
        'description',
        'slug',
        'status',
        'database',
    ];

    public function BlogPostCategories(): HasMany
    {
        return $this->hasMany(BlogPostCategory::class);
    }

    public function BlogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    public function currencyCategories(): HasMany
    {
        return $this->hasMany(CurrencyCategory::class);
    }

    public function faqCategories(): HasMany
    {
        return $this->hasMany(FaqCategory::class);
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }

    public function geographicalCities(): HasMany
    {
        return $this->hasMany(GeographicalCity::class);
    }

    public function geographicalCountries(): HasMany
    {
        return $this->hasMany(GeographicalCountry::class);
    }

    public function geographicalNeighborhoods(): HasMany
    {
        return $this->hasMany(GeographicalNeighborhood::class);
    }

    public function geographicalStates(): HasMany
    {
        return $this->hasMany(GeographicalState::class);
    }

    public function geographicalZones(): HasMany
    {
        return $this->hasMany(GeographicalZone::class);
    }

    public function LanguageLines(): HasMany
    {
        return $this->hasMany(LanguageLine::class);
    }

    public function Languages(): HasMany
    {
        return $this->hasMany(Language::class);
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function pageCategories(): HasMany
    {
        return $this->hasMany(PageCategory::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function productPrices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function tenantDomains(): HasMany
    {
        return $this->hasMany(TenantDomain::class);
    }
}
