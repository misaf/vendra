<?php

declare(strict_types=1);

namespace App\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

final class DefaultPathGenerator implements PathGenerator
{
    /**
     * @param Media $media
     * @return string
     */
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media) . '/';
    }

    /**
     * @param Media $media
     * @return string
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media) . '/conversions/';
    }

    /**
     * @param Media $media
     * @return string
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media) . '/responsive-images/';
    }

    /**
     * @param Media $media
     * @return string
     */
    private function getBasePath(Media $media): string
    {
        $prefix = config('media-library.prefix', '');

        if ('' !== $prefix) {
            return $prefix . '/' . $media->uuid;
        }

        return $media->uuid;
    }
}
