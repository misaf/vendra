<?php

declare(strict_types=1);

return [
    'namespace' => 'JsonApi',

    'servers' => [
        'vendra-blog'       => Misaf\VendraBlogApi\JsonApi\V1\Server::class,
        'vendra-faq'        => Misaf\VendraFaqApi\JsonApi\V1\Server::class,
        'vendra-multimedia' => Misaf\VendraMultimediaApi\JsonApi\V1\Server::class,
        'vendra-product'    => Misaf\VendraProductApi\JsonApi\V1\Server::class,
    ],
];
