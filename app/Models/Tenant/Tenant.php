<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Casts\DateCast;
use App\Models\Blog\BlogPost;
use App\Models\Blog\BlogPostCategory;
use App\Models\Currency;
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
use App\Traits\ActivityLog;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use Spatie\Tags\Tag;

final class Tenant extends SpatieTenant implements
    Currency\Contracts\HasCurrency,
    User\Contracts\HasUserProfile,
    User\Contracts\HasUserProfileBalance,
    User\Contracts\HasUserProfileDocument,
    User\Contracts\HasUserProfilePhone
{
    use ActivityLog;

    use Currency\Traits\HasCurrency;

    use HasFactory;

    use HasSlugOptionsTrait;

    use User\Traits\HasUserProfile;

    use User\Traits\HasUserProfileBalance;

    use User\Traits\HasUserProfileDocument;

    use User\Traits\HasUserProfilePhone;

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

    /**
     * Get the user that owns the profile.
     *
     * @return HasMany
     */
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
}
