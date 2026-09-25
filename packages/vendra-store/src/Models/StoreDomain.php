<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Misaf\VendraStore\Concerns\BelongsToStore;
use Misaf\VendraStore\Database\Factories\StoreDomainFactory;
use Misaf\VendraStore\Support\StorefrontSettings;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Every active domain resolves the store, and the primary one is its canonical host.
 * Replaced domains are kept trashed as history.
 *
 * @property int $id
 * @property int $store_id
 * @property string $name
 * @property string $description
 * @property string $slug
 * @property bool $active
 * @property bool $is_primary
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['name', 'description', 'slug', 'active', 'is_primary'])]
#[UseFactory(StoreDomainFactory::class)]
final class StoreDomain extends Model implements ShouldLogActivity
{
    use BelongsToStore;

    /** @use HasFactory<StoreDomainFactory> */
    use HasFactory;

    use HasSlug;
    use SoftDeletes;

    public const string DOMAIN_PATTERN = '/^(?!-)[A-Za-z0-9-]{1,63}(?<!-)(\.(?!-)[A-Za-z0-9-]{1,63}(?<!-))+$/';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'description' => 'string',
            'slug' => 'string',
            'active' => 'boolean',
            'is_primary' => 'boolean',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function inactive(Builder $query): Builder
    {
        return $query->where('active', false);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function primary(Builder $query): Builder
    {
        return $query->where('is_primary', true);
    }

    /**
     * @return array<int, string|Unique>
     */
    public static function activeDomainRules(): array
    {
        return [
            'required',
            'string',
            'max:255',
            'regex:'.self::DOMAIN_PATTERN,
            // `admin.<domain>` is the administration host of the store owning <domain>.
            'not_regex:/^admin\./i',
            Rule::unique(self::class, 'name')->where('active', true)->withoutTrashed(),
            /*
             | An offboarded store keeps its deployment row, and with it the
             | deployment's unique domain, after its store domain is released.
             */
            Rule::unique(StorefrontDeployment::class, 'domain'),
        ];
    }

    public static function normalizeDomain(string $domain): string
    {
        return Str::lower(mb_trim($domain));
    }

    /**
     * Determine whether the domain lies outside the platform's storefront base domain.
     *
     * Without a base domain there is nothing to tell a custom domain from.
     */
    public static function isCustom(string $domain): bool
    {
        $baseDomain = Str::lower(resolve(StorefrontSettings::class)->baseDomain);

        if ($baseDomain === '') {
            return false;
        }

        return ! Str::endsWith(self::normalizeDomain($domain), '.'.$baseDomain);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->preventOverwrite();
    }

    /**
     * @return Attribute<string, string>
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => self::normalizeDomain($value),
        );
    }
}
