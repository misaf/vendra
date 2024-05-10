<?php

declare(strict_types=1);

namespace App\JsonApi\V1;

use LaravelJsonApi\Core\Server\Server as BaseServer;

final class Server extends BaseServer
{
    protected string $baseUri = '/api/v1';

    /**
     * Determine if the server is authorizable.
     *
     * @return bool
     */
    public function authorizable(): bool
    {
        return true;
    }

    public function serving(): void {}

    /**
     * Get the server's list of schemas.
     *
     * @return array
     */
    protected function allSchemas(): array
    {
        return [
            BlogPostCategories\BlogPostCategorySchema::class,
            BlogPosts\BlogPostSchema::class,
            FaqCategories\FaqCategorySchema::class,
            Faqs\FaqSchema::class,
            Multimedia\MultimediaSchema::class,
            ProductCategories\ProductCategorySchema::class,
            ProductPrices\ProductPriceSchema::class,
            Products\ProductSchema::class,
        ];
    }
}
