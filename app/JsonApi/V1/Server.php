<?php

declare(strict_types=1);

namespace App\JsonApi\V1;

use LaravelJsonApi\Core\Server\Server as BaseServer;

final class Server extends BaseServer
{
    protected string $baseUri = '/api/v1';

    public function serving(): void
    {
        // no-op
    }

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
