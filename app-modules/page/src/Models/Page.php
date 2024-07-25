<?php

declare(strict_types=1);

namespace Termehsoft\Page\Models;

use App\Models\BaseModelWithMedia;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Page extends BaseModelWithMedia
{
    use HasSlugOptionsTrait;
    use SoftDeletes;

    protected $casts = [
        'id'               => 'integer',
        'page_category_id' => 'integer',
        'name'             => 'string',
        'description'      => 'string',
        'slug'             => 'string',
        'status'           => 'boolean',
    ];

    protected $fillable = [
        'page_category_id',
        'name',
        'description',
        'slug',
        'status',
    ];

    public function pageCategory(): BelongsTo
    {
        return $this->belongsTo(PageCategory::class);
    }
}
