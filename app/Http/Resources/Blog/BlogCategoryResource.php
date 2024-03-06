<?php

declare(strict_types=1);

namespace App\Http\Resources\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class BlogCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name'                => $this->getTranslations('name'),
            'description'         => $this->getTranslations('description'),
            'slug'                => $this->getTranslations('slug'),
            'media'               => $this->media->map(fn($item) => $item->toHtml())
        ];
    }
}
