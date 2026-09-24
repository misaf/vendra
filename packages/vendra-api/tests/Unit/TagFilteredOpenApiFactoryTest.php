<?php

declare(strict_types=1);

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Info;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\OpenApi;
use Misaf\VendraApi\OpenApi\TagFilteredOpenApiFactory;
use Symfony\Component\HttpFoundation\Request;

it('drops unrequested head, options, and trace operations', function (): void {
    $paths = new Paths;
    $paths->addPath('/products', new PathItem(
        get: new Operation(operationId: 'getProducts', tags: ['Product']),
        options: new Operation(operationId: 'optionsProducts', tags: ['Order']),
        head: new Operation(operationId: 'headProducts', tags: ['Order']),
        trace: new Operation(operationId: 'traceProducts', tags: ['Order']),
    ));
    $paths->addPath('/orders', new PathItem(
        head: new Operation(operationId: 'headOrders', tags: ['Order']),
    ));

    $decorated = new readonly class(new OpenApi(new Info('Vendra', '1.0'), [], $paths)) implements OpenApiFactoryInterface
    {
        public function __construct(private OpenApi $openApi) {}

        public function __invoke(array $context = []): OpenApi
        {
            return $this->openApi;
        }
    };

    $openApi = (new TagFilteredOpenApiFactory($decorated))(['request' => Request::create('/api/docs', parameters: ['tags' => 'Product'])]);
    $filtered = $openApi->getPaths()->getPaths();
    ['/products' => $products] = $filtered;

    expect(array_keys($filtered))->toBe(['/products'])
        ->and($products->getGet()?->getOperationId())->toBe('getProducts')
        ->and($products->getOptions())->toBeNull()
        ->and($products->getHead())->toBeNull()
        ->and($products->getTrace())->toBeNull();
});
