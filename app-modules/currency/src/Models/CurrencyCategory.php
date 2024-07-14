<?php

declare(strict_types=1);

namespace Termehsoft\Currency\Models;

use App\Casts\DateCast;
use App\Models\TenantWithMedia;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

final class CurrencyCategory extends TenantWithMedia implements Contracts\HasCurrency
{
    use HasSlugOptionsTrait;
    use SoftDeletes;
    use Traits\HasCurrency;

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
