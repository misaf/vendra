<?php

declare(strict_types=1);

namespace Termehsoft\Page\Models;

use App\Models\BaseModelWithMedia;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class PageCategory extends BaseModelWithMedia
{
    use HasSlugOptionsTrait;
    use SoftDeletes;

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
