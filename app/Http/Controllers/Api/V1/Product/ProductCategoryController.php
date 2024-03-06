<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductCategoryResource;
use App\Models\Product\ProductCategory;
use Spatie\QueryBuilder\QueryBuilder;

final class ProductCategoryController extends Controller
{
    public function index()
    {
        $query = QueryBuilder::for(ProductCategory::class)
            ->allowedIncludes([
                'media'
            ])
            ->allowedFilters(['name', 'slug', 'status'])
            ->allowedSorts('position')
            ->defaultSort('-position');

        $perPage = request()->query('per_page', 10);
        $paginatedPosts = $query->paginate($perPage)->appends(request()->except('page'));

        return ProductCategoryResource::collection($paginatedPosts);
    }
}
