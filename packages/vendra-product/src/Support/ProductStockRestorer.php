<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Support;

use Misaf\VendraProduct\Actions\RestockProductsAction;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraSupport\Contracts\StockRestorer;

final readonly class ProductStockRestorer implements StockRestorer
{
    public function __construct(private RestockProductsAction $restockProducts) {}

    public function sellableType(): string
    {
        return (new Product)->getMorphClass();
    }

    public function restore(array $quantities): void
    {
        $this->restockProducts->execute($quantities);
    }
}
