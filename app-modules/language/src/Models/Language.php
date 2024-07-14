<?php

declare(strict_types=1);

namespace Termehsoft\Language\Models;

use App\Casts\DateCast;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\SortableTrait;
use Termehsoft\Tenant\Models\TenantWithMedia;
use Termehsoft\Tenant\Scopes\Tenant as TenantScope;

#[ScopedBy(TenantScope::class)]
final class Language extends TenantWithMedia
{
    use HasSlugOptionsTrait;
    use SoftDeletes;
    use SortableTrait;

    protected $casts = [
        'id'          => 'integer',
        'name'        => 'string',
        'description' => 'string',
        'slug'        => 'string',
        'iso_code'    => 'string',
        'is_default'  => 'boolean',
        'position'    => 'integer',
        'status'      => 'boolean',
        'created_at'  => DateCast::class,
        'updated_at'  => DateCast::class,
        'deleted_at'  => DateCast::class,
    ];

    protected $fillable = [
        'name',
        'description',
        'slug',
        'iso_code',
        'is_default',
        'position',
        'status',
    ];
}
