<?php

declare(strict_types=1);

namespace App\Models\Language;

use App\Casts\DateCast;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Spatie\TranslationLoader\LanguageLine as TranslationLoaderLanguageLine;

final class LanguageLine extends Tenant
{
    use HasTranslations;

    use SoftDeletes;

    protected $casts = [
        'id'         => 'integer',
        'group'      => 'string',
        'key'        => 'string',
        'text'       => 'array',
        'created_at' => DateCast::class,
        'updated_at' => DateCast::class,
        'deleted_at' => DateCast::class,
    ];

    protected $fillable = [
        'group',
        'key',
        'text',
    ];

    protected $translatable = ['text'];

    public static function boot(): void
    {
        parent::boot();

        $flushGroupCache = fn(self $languageLine) => $languageLine->flushGroupCache();
        $flushGroupCache2 = fn(self $languageLine) => $languageLine->flushGroupCache2();

        static::updating($flushGroupCache2);
        static::saved($flushGroupCache);
        static::deleted($flushGroupCache);
    }

    private function flushGroupCache(): void
    {

        (new TranslationLoaderLanguageLine([
            'group' => $this->group,
            'key'   => $this->key,
            'text'  => $this->getTranslations('text'),
        ]))
            ->fireModelEvent('saved');
    }

    private function flushGroupCache2(): void
    {
        (new TranslationLoaderLanguageLine([
            'group' => $this->getOriginal('group'),
            'key'   => $this->key,
            'text'  => $this->getTranslations('text'),
        ]))
            ->fireModelEvent('updating');
    }
}
