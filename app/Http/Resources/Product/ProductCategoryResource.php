<?php

declare(strict_types=1);

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->getTranslations('name'),
            'description'         => $this->getTranslations('description'),
            'slug'                => $this->getTranslations('slug'),
            'media'               => $this->media->map(fn($item) => $item->toHtml())
        ];
    }
}
