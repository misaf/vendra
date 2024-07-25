<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class BaseModelWithMedia extends BaseModel implements HasMedia
{
    use InteractsWithMedia, ThumbnailTableRecord {
        ThumbnailTableRecord::registerMediaCollections insteadof InteractsWithMedia;
        ThumbnailTableRecord::registerMediaConversions insteadof InteractsWithMedia;
    }

    public function multimedia(): MorphMany
    {
        return $this->media();
    }
}
