<?php

declare(strict_types=1);

namespace Termehsoft\Page\Models;

use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Termehsoft\Tenant\Models\TenantWithMedia;

final class PageCategory extends TenantWithMedia
{
    use HasSlugOptionsTrait;

    protected $casts = [
        'id'          => 'integer',
        'name'        => 'string',
        'description' => 'string',
        'slug'        => 'string',
        'status'      => 'boolean',
    ];

    protected $fillable = [
        'name',
        'description',
        'slug',
        'status',
    ];

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }
}
