<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
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
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * One of a store's domains. The active one (active = true) is what resolves the
 * store from a request host; replaced domains are kept, trashed and inactive, as
 * history.
 *
 * @property int $id
 * @property int $store_id
 * @property string $name
 * @property string $description
 * @property string $slug
 * @property bool $active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['name', 'description', 'slug', 'active'])]
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
        ];
    }

    /**
     * @param  Builder<StoreDomain>  $query
     * @return Builder<StoreDomain>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * @param  Builder<StoreDomain>  $query
     * @return Builder<StoreDomain>
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('active', false);
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
            Rule::unique(self::class, 'name')->where('active', true)->withoutTrashed(),
        ];
    }

    public static function normalizeDomain(string $domain): string
    {
        return Str::lower(mb_trim($domain));
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
