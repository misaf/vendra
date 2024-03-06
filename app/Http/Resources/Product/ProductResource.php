<?php

declare(strict_types=1);

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'product_category_id' => $this->product_category_id,
            'name'                => $this->getTranslations('name'),
            'description'         => $this->getTranslations('description'),
            'slug'                => $this->getTranslations('slug'),
            'token'               => $this->token,
            'quantity'            => $this->quantity,
            'stock_threshold'     => $this->stock_threshold,
            'in_stock'            => $this->in_stock,
            'available_soon'      => $this->available_soon,
            'availability_date'   => $this->availability_date,
            'media'               => $this->media->map(fn($item) => $item->toHtml())
        ];
    }
}
