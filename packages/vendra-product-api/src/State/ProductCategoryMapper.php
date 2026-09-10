<?php

declare(strict_types=1);

namespace Misaf\VendraProductApi\State;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraApi\State\ResourceMapper;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraProductApi\ApiResource\ProductCategoryResource;
use Misaf\VendraProductApi\State\Concerns\MapsCatalogResources;
use UnexpectedValueException;

final class ProductCategoryMapper implements ResourceMapper
{
    use MapsCatalogResources;

    public function map(Model $model): ProductCategoryResource
    {
        throw_unless($model instanceof ProductCategory, UnexpectedValueException::class, 'Expected a product category model.');

        return $this->toCategoryResource($model);
    }
}
