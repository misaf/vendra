<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product\Product;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class ProductController extends Controller
{
    public function index()
    {
        $query = QueryBuilder::for(Product::class)
            ->allowedIncludes(['productCategory', 'media'])
            ->allowedFilters([
                AllowedFilter::callback('product_category.slug', function ($query, $value): void {
                    $query->whereRelation('productCategory', 'product_categories.slug->fa', $value);
                }),
                'name',
                'slug',
                'in_stock'
            ])
            ->allowedSorts('position')
            ->defaultSort('-position');

        $perPage = request()->query('per_page', 10);
        $paginatedPosts = $query->paginate($perPage)->appends(request()->except('page'));

        return ProductResource::collection($paginatedPosts);
    }

    public function show(string $id)
    {
        return ProductResource::collection(Product::find($id));
    }
}
