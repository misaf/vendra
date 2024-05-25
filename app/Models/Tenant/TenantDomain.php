<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Casts\DateCast;
use App\Models\Tenant;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

final class TenantDomain extends Tenant
{
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
