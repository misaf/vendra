<?php

declare(strict_types=1);

namespace App\Models\Faq;

use App\Casts\DateCast;
use App\Models\TenantWithMedia;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Translatable\HasTranslations;

final class FaqCategory extends TenantWithMedia
{
    use HasSlugOptionsTrait;
    use HasTranslatableSlug;
    use HasTranslations;
    use SoftDeletes;

    public $translatable = ['name', 'description', 'slug'];

    protected $casts = [
        'id'          => 'integer',
        'name'        => 'array',
        'description' => 'array',
        'slug'        => 'array',
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

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }

    public function multimedia(): MorphMany
    {
        return $this->media();
    }
}
