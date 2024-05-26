<?php

declare(strict_types=1);

namespace App\Models\Currency;

use App\Casts\DateCast;
use App\Models\TenantWithMedia;
use App\Models\User;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

final class Currency extends TenantWithMedia implements
    Contracts\BelongsToCurrencyCategory,
    Sortable,
    User\Contracts\HasUserProfileBalance
{
    use HasSlugOptionsTrait;
    use SoftDeletes;
    use SortableTrait;
    use User\Traits\HasUserProfileBalance;

    protected $casts = [
        'id'                   => 'integer',
        'currency_category_id' => 'integer',
        'name'                 => 'string',
        'description'          => 'string',
        'slug'                 => 'string',
        'iso_code'             => 'string',
        'conversion_rate'      => 'float',
        'decimal_place'        => 'integer',
        'is_default'           => 'boolean',
        'position'             => 'integer',
        'status'               => 'boolean',
        'created_at'           => DateCast::class,
        'updated_at'           => DateCast::class,
        'deleted_at'           => DateCast::class,
    ];

    protected $fillable = [
        'currency_category_id',
        'name',
        'description',
        'slug',
        'iso_code',
        'conversion_rate',
        'decimal_place',
        'is_default',
        'position',
        'status',
    ];

    /**
     * Get the currency category that owns the currency.
     *
     * @return BelongsTo
     */
    public function currencyCategory(): BelongsTo
    {
        return $this->belongsTo(CurrencyCategory::class);
    }
}
