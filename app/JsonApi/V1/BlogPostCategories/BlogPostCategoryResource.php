<?php

declare(strict_types=1);

namespace App\JsonApi\V1\BlogPostCategories;

use App\Traits\LocalizableAttributesTrait;
use LaravelJsonApi\Core\Resources\JsonApiResource;

final class BlogPostCategoryResource extends JsonApiResource
{
    use LocalizableAttributesTrait;

    /**
     * Get the resource's attributes.
     *
     * @param Request|null $request
     * @return iterable
     */
    public function attributes($request): iterable
    {
        $locale = app()->getLocale();

        return [
            'name'         => $this->getLocalizedAttribute('name', $locale) ?: null,
            'description'  => $this->getLocalizedAttribute('description', $locale) ?: null,
            'slug'         => $this->getLocalizedAttribute('slug', $locale) ?: null,
            'position'     => $this->position,
            'status'       => $this->status,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }

    /**
     * Get the resource's relationships.
     *
     * @param Request|null $request
     * @return iterable
     */
    public function relationships($request): iterable
    {
        return [
            $this->relation('blogPosts'),
            $this->relation('multimedia'),
        ];
    }
}
