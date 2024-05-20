<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Casts\DateCast;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Tenant extends Model
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
        'domain',
        'database',
    ];

    public function BlogPostCategories(): HasMany
    {
        return $this->hasMany(
            related: \App\Models\Blog\BlogPostCategory::class,
        );
    }

    public function BlogPosts(): HasMany
    {
        return $this->hasMany(
            related: \App\Models\Blog\BlogPost::class,
        );
    }

    public function currencies(): HasMany
    {
        return $this->hasMany(
            related: \App\Models\Currency\Currency::class,
        );
    }

    public function currencyCategories(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Currency\CurrencyCategory::class,
        );
    }

    public function faqCategories(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Faq\FaqCategory::class,
        );
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Faq\Faq::class,
        );
    }

    public function geographicalCities(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Geographical\GeographicalCity::class,
        );
    }

    public function geographicalCountries(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Geographical\GeographicalCountry::class,
        );
    }

    public function geographicalNeighborhoods(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Geographical\GeographicalNeighborhood::class,
        );
    }

    public function geographicalStates(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Geographical\GeographicalState::class,
        );
    }

    public function geographicalZones(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Geographical\GeographicalZone::class,
        );
    }

    public function LanguageLines(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Language\LanguageLine::class,
        );
    }

    public function Languages(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Language\Language::class,
        );
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Order\OrderProduct::class,
        );
    }

    public function orders(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Order\Order::class,
        );
    }

    public function pageCategories(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Page\PageCategory::class,
        );
    }

    public function pages(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Page\Page::class,
        );
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Permission\Permission::class,
        );
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Product\ProductCategory::class,
        );
    }

    public function productPrices(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Product\ProductPrice::class,
        );
    }

    public function products(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Product\Product::class,
        );
    }

    public function roles(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\Permission\Role::class,
        );
    }

    public function tags(): HasMany
    {
        return $this->hasMany(
            related:\Spatie\Tags\Tag::class,
        );
    }

    public function userProfileBalances(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\User\UserProfileBalance::class,
        );
    }

    public function userProfileDocuments(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\User\UserProfileDocument::class,
        );
    }

    public function userProfilePhones(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\User\UserProfilePhone::class,
        );
    }

    public function userProfiles(): HasMany
    {
        return $this->hasMany(
            related:\App\Models\User\UserProfile::class,
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            related:\App\Models\User::class,
        );
    }
}
