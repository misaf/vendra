<?php

declare(strict_types=1);

namespace Misaf\VendraMultimediaApi\State;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraApi\State\ResourceMapper;
use Misaf\VendraMultimediaApi\ApiResource\MultimediaResource;
use UnexpectedValueException;

final class MultimediaMapper implements ResourceMapper
{
    public function map(Model $model): MultimediaResource
    {
        if (! $model instanceof PublicMultimedia) {
            throw new UnexpectedValueException('Expected a public multimedia model.');
        }

        return MultimediaResourceFactory::make($model);
    }
}
