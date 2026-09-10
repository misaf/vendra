<?php

declare(strict_types=1);

namespace Misaf\VendraMultimediaApi\State;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraMultimediaApi\ApiResource\MultimediaResource;
use Throwable;
use UnexpectedValueException;

final class MultimediaResourceFactory
{
    public static function make(Model $media): MultimediaResource
    {
        $id = $media->getKey();
        $uuid = $media->getAttribute('uuid');
        $name = $media->getAttribute('name');
        $fileName = $media->getAttribute('file_name');
        $collection = $media->getAttribute('collection_name');
        $mimeType = $media->getAttribute('mime_type');
        $bytes = $media->getAttribute('size');
        $generatedConversions = $media->getAttribute('generated_conversions');

        if (is_string($id) && ctype_digit($id)) {
            $id = (int) $id;
        }

        if (is_string($bytes) && ctype_digit($bytes)) {
            $bytes = (int) $bytes;
        }

        if (! is_int($id) || ! is_int($bytes)) {
            throw new UnexpectedValueException('Media identifiers and sizes must be integers.');
        }

        if (! is_string($uuid) || ! is_string($name) || ! is_string($fileName) || ! is_string($collection)) {
            throw new UnexpectedValueException('Media names and identifiers must be strings.');
        }

        if ($mimeType !== null && ! is_string($mimeType)) {
            throw new UnexpectedValueException('Media MIME types must be strings or null.');
        }

        if ($generatedConversions === null) {
            $generatedConversions = [];
        }

        if (! is_array($generatedConversions)) {
            throw new UnexpectedValueException('Media generated conversions must be an array.');
        }

        $normalizedConversions = [];

        foreach ($generatedConversions as $key => $value) {
            if (is_string($key)) {
                $normalizedConversions[$key] = $value;
            }
        }

        $disk = $media->getAttribute('disk');
        $customProperties = $media->getAttribute('custom_properties');
        $responsiveImages = $media->getAttribute('responsive_images');

        if (! is_string($disk)) {
            $disk = '';
        }

        if ($customProperties === null) {
            $customProperties = [];
        }

        if (! is_array($customProperties)) {
            $customProperties = [];
        }

        if ($responsiveImages === null) {
            $responsiveImages = [];
        }

        if (! is_array($responsiveImages)) {
            $responsiveImages = [];
        }

        return new MultimediaResource(
            id: $id,
            uuid: $uuid,
            name: $name,
            fileName: $fileName,
            collection: $collection,
            mimeType: $mimeType,
            bytes: $bytes,
            disk: $disk,
            url: self::safeUrl($media),
            generatedConversions: $normalizedConversions,
            customProperties: $customProperties,
            responsiveImages: $responsiveImages,
        );
    }

    private static function safeUrl(Model $media): ?string
    {
        if (! method_exists($media, 'getUrl')) {
            return null;
        }

        try {
            $url = $media->getUrl();

            return is_string($url) ? $url : null;
        } catch (Throwable) {
            return null;
        }
    }
}
