<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Support;

use Illuminate\Support\Facades\Config;
use Misaf\VendraSupport\Tenancy\TenantSchema;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * Store each tenant's media under its own directory.
 *
 * The tenant comes from the media row, not the current tenant, because paths are
 * rebuilt on every read, and reads also happen outside tenant context.
 */
final class DefaultPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media).'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media).'/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media).'/responsive-images/';
    }

    /**
     * Get the directory used before media was grouped by tenant.
     */
    public function getLegacyBasePath(Media $media): string
    {
        $prefix = Config::string('media-library.prefix', '');

        if ($prefix !== '') {
            return $prefix.'/'.$media->uuid;
        }

        return $media->uuid;
    }

    public function getBasePath(Media $media): string
    {
        $tenantKey = $media->getAttribute(TenantSchema::column());

        if ($tenantKey === null) {
            return $this->getLegacyBasePath($media);
        }

        $prefix = Config::string('media-library.prefix', '');

        if ($prefix !== '') {
            return $prefix.'/'.$tenantKey.'/'.$media->uuid;
        }

        return $tenantKey.'/'.$media->uuid;
    }
}
