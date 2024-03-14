<?php

declare(strict_types=1);

namespace App\Traits;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait ThumbnailTableRecord
{
    public function registerMediaCollections(): void
    {
        $this->addMediaConversion('thumb-table')->width(48)->format('webp');
        $this->addMediaConversion('small')->width(300)->format('webp');
        $this->addMediaConversion('medium')->width(500)->format('webp');
        $this->addMediaConversion('large')->width(800)->format('webp');
        $this->addMediaConversion('extra-large')->width(1200)->format('webp');
    }

    public function registerMediaConversions(?Media $media = null): void {}
}
