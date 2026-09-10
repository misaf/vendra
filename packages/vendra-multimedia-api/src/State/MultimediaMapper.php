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
        throw_unless($model instanceof PublicMultimedia, UnexpectedValueException::class, 'Expected a public multimedia model.');

        return MultimediaResourceFactory::make($model);
    }
}
