<?php

declare(strict_types=1);

namespace Termehsoft\Currency\Models;

use App\Casts\DateCast;
use App\Models\BaseModelWithMedia;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Termehsoft\Currency\Contracts\HasCurrency as HasCurrencyInterface;
use Termehsoft\Currency\Traits\HasCurrency as HasCurrencyTrait;

final class CurrencyCategory extends BaseModelWithMedia implements
    HasCurrencyInterface
{
    use HasCurrencyTrait;
    use HasSlugOptionsTrait;
    use SoftDeletes;

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
    ];
}
