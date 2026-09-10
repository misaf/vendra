<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMediaLibrary;

/**
 * @property int $tenant_id
 */
#[Hidden(['tenant_id'])]
final class Multimedia extends SpatieMediaLibrary
{
    use BelongsToTenant;
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            ...parent::casts(),
        ];
    }
}
