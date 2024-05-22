<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Casts\DateCast;
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
use App\Models\Page\Page;
use App\Models\Page\PageCategory;
use App\Models\Permission\Permission;
use App\Models\Permission\Role;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Product\ProductPrice;
use App\Models\User;
use App\Models\User\UserProfile;
use App\Models\User\UserProfileBalance;
use App\Models\User\UserProfileDocument;
use App\Models\User\UserProfilePhone;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use Spatie\Tags\Tag;

final class Tenant extends SpatieTenant
{
    use HasFactory;

    use HasSlugOptionsTrait;

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

    public function currencies(): HasMany
    {
        return $this->hasMany(Currency::class);
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

    public function userProfileBalances(): HasMany
    {
        return $this->hasMany(UserProfileBalance::class);
    }

    public function userProfileDocuments(): HasMany
    {
        return $this->hasMany(UserProfileDocument::class);
    }

    public function userProfilePhones(): HasMany
    {
        return $this->hasMany(UserProfilePhone::class);
    }

    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
