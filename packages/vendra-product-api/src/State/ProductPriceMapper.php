<?php

declare(strict_types=1);

namespace Misaf\VendraProductApi\State;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraApi\State\ResourceMapper;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraProductApi\ApiResource\ProductPriceResource;
use Misaf\VendraProductApi\State\Concerns\MapsCatalogResources;
use UnexpectedValueException;

final class ProductPriceMapper implements ResourceMapper
{
    use MapsCatalogResources;

    public function map(Model $model): ProductPriceResource
    {
        if (! $model instanceof ProductPrice) {
            throw new UnexpectedValueException('Expected a product price model.');
        }

        return $this->toPriceResource($model);
    }
}
