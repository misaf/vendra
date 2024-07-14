<?php

declare(strict_types=1);

namespace Termehsoft\Tenant\Models;

use App\Traits\ThumbnailTableRecord;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TenantWithMedia extends Tenant implements
    HasMedia
{
    use InteractsWithMedia, ThumbnailTableRecord {
        ThumbnailTableRecord::registerMediaCollections insteadof InteractsWithMedia;
        ThumbnailTableRecord::registerMediaConversions insteadof InteractsWithMedia;
    }
}
