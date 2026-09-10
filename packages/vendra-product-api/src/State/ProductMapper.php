<?php

declare(strict_types=1);

namespace Misaf\VendraProductApi\State;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraApi\State\ResourceMapper;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProductApi\ApiResource\ProductResource;
use Misaf\VendraProductApi\State\Concerns\MapsCatalogResources;
use UnexpectedValueException;

final class ProductMapper implements ResourceMapper
{
    use MapsCatalogResources;

    public function map(Model $model): ProductResource
    {
        throw_unless($model instanceof Product, UnexpectedValueException::class, 'Expected a product model.');

        return $this->toProductResource($model);
    }
}
