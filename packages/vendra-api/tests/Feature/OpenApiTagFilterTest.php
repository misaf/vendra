<?php

declare(strict_types=1);

use Illuminate\Support\Arr;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('documents every resource when no tag is requested', function (): void {
    $paths = $this->getJson('/api/docs.jsonopenapi', ['Accept' => 'application/vnd.openapi+json'])
        ->assertOk()
        ->json('paths');

    expect($paths)->toHaveKey('/api/catalog/products')
        ->and(array_keys($paths))->not->toEqual(['/api/catalog/products', '/api/catalog/products/{id}']);
});

it('restricts the documentation to the requested tag', function (): void {
    $document = $this->getJson('/api/docs.jsonopenapi?tags=Product', ['Accept' => 'application/vnd.openapi+json'])
        ->assertOk()
        ->json();

    expect(array_keys(Arr::get($document, 'paths')))
        ->toEqualCanonicalizing(['/api/catalog/products', '/api/catalog/products/{id}']);

    foreach (Arr::get($document, 'paths') as $pathItem) {
        foreach ($pathItem as $operation) {
            expect(Arr::get($operation, 'tags'))->toContain('Product');
        }
    }

    expect(array_column(Arr::get($document, 'tags', []), 'name'))->toEqual(['Product']);

    $schemas = array_keys(Arr::get($document, 'components.schemas', []));

    expect($schemas)->not->toBeEmpty()
        ->and($schemas)->toContain('Product.jsonld')
        ->and(array_filter($schemas, fn (string $schema): bool => str_starts_with($schema, 'CustomPage')))->toBeEmpty();
});

it('accepts several comma separated tags', function (): void {
    $paths = $this->getJson('/api/docs.jsonopenapi?tags=Product,ProductCategory', ['Accept' => 'application/vnd.openapi+json'])
        ->assertOk()
        ->json('paths');

    expect(array_keys($paths))->toEqualCanonicalizing([
        '/api/catalog/products',
        '/api/catalog/products/{id}',
        '/api/catalog/product-categories',
        '/api/catalog/product-categories/{id}',
    ]);
});
