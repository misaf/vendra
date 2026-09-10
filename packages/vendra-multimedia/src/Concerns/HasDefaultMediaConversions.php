<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Concerns;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @phpstan-require-implements HasMedia
 */
trait HasDefaultMediaConversions /** @phpstan-ignore trait.unused */
{
    public function registerMediaConversions(?Media $media = null): void
    {
        foreach ($this->mediaConversionWidths() as $name => $width) {
            $this->addMediaConversion($name)
                ->width($width)
                ->format($this->mediaConversionFormat());
        }
    }

    /**
     * @return array<string, int>
     */
    protected function mediaConversionWidths(): array
    {
        return [
            'thumb-table' => 48,
            'small' => 300,
            'medium' => 500,
            'large' => 800,
            'extra-large' => 1200,
        ];
    }

    protected function mediaConversionFormat(): string
    {
        return 'webp';
    }
}
