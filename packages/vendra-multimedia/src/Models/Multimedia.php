<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Misaf\VendraMultimedia\Observers\MultimediaObserver;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMediaLibrary;

/**
 * @property int $tenant_id
 */
#[Hidden(['tenant_id'])]
#[ObservedBy([MultimediaObserver::class])]
final class Multimedia extends SpatieMediaLibrary
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            ...parent::casts(),
        ];
    }
}
