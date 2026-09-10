<?php

declare(strict_types=1);

namespace Misaf\VendraMultimediaApi\State;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Misaf\VendraMultimedia\Models\Multimedia;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

/**
 * Read model for public media API operations.
 */
#[Unguarded]
#[Table(name: 'media')]
final class PublicMultimedia extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'manipulations' => 'array',
            'custom_properties' => 'array',
            'generated_conversions' => 'array',
            'responsive_images' => 'array',
        ];
    }

    public function getUrl(): string
    {
        $media = (new Multimedia)->newFromBuilder($this->getAttributes());

        return $media->getUrl();
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function scope(Builder $query): Builder
    {
        return $query->whereIn('disk', self::diskNames());
    }

    public static function isPublic(Model $media): bool
    {
        $disk = $media->getAttribute('disk');

        return is_string($disk) && in_array($disk, self::diskNames(), true);
    }

    /**
     * @return array<int, string>
     */
    private static function diskNames(): array
    {
        $publicDisks = [];

        foreach (Config::array('filesystems.disks', []) as $disk => $configuration) {
            if (
                is_string($disk)
                && is_array($configuration)
                && 'public' === (Arr::get($configuration, 'visibility', null))
            ) {
                $publicDisks[] = $disk;
            }
        }

        return $publicDisks;
    }
}
