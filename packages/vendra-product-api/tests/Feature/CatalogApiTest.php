<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Database\Factories\ProductPriceFactory;
use Misaf\VendraProduct\Models\ProductPrice;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('filters catalog items and exposes group and price relationships', function (): void {
    $group = ProductCategoryFactory::new()->active()->create();
    $item = ProductFactory::new()->forCategory($group)->create(['in_stock' => true]);
    $price = ProductPriceFactory::new()->forProduct($item)->forCurrencyCode('USD')->create();
    ProductFactory::new()->forCategory($group)->create(['in_stock' => false]);

    $this->getJson("/api/catalog/products?inStock=1&categoryId={$group->id}", ['Accept' => 'application/ld+json'])
        ->assertOk()
        ->assertJsonPath('totalItems', 1)
        ->assertJsonPath('member.0.id', $item->id)
        ->assertJsonPath('member.0.productCategory.id', $group->id)
        ->assertJsonPath('member.0.productPrices.0', "/api/catalog/product-prices/{$price->id}");

    $this->getJson("/api/catalog/product-prices?currency=USD&productId={$item->id}", ['Accept' => 'application/ld+json'])
        ->assertOk()
        ->assertJsonPath('totalItems', 1)
        ->assertJsonPath('member.0.product.id', $item->id);
});

it('hides prices belonging to products in inactive categories', function (): void {
    $inactiveCategory = ProductCategoryFactory::new()->inactive()->create();
    $hiddenProduct = ProductFactory::new()->forCategory($inactiveCategory)->create();
    $hiddenPrice = ProductPriceFactory::new()->forProduct($hiddenProduct)->forCurrencyCode('USD')->create();

    $this->getJson("/api/catalog/product-prices/{$hiddenPrice->id}", ['Accept' => 'application/ld+json'])
        ->assertNotFound();

    $this->getJson('/api/catalog/product-prices', ['Accept' => 'application/ld+json'])
        ->assertOk()
        ->assertJsonPath('totalItems', 0)
        ->assertJsonMissing(['id' => $hiddenPrice->id]);
});

it('embeds only public multimedia on catalog products', function (): void {
    $category = ProductCategoryFactory::new()->active()->create();
    $product = ProductFactory::new()->forCategory($category)->create();
    $mediaAttributes = [
        'collection_name' => 'catalog',
        'mime_type' => 'image/jpeg',
        'size' => 2048,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ];
    $publicAsset = $product->multimedia()->create([
        ...$mediaAttributes,
        'uuid' => (string) Str::uuid(),
        'name' => 'Public product image',
        'file_name' => 'public-product.jpg',
        'disk' => 'public',
        'conversions_disk' => 'public',
    ]);
    $privateAsset = $product->multimedia()->create([
        ...$mediaAttributes,
        'uuid' => (string) Str::uuid(),
        'name' => 'Private product image',
        'file_name' => 'private-product.jpg',
        'disk' => 'private',
        'conversions_disk' => 'private',
    ]);

    $this->getJson("/api/catalog/products/{$product->id}", ['Accept' => 'application/ld+json'])
        ->assertOk()
        ->assertJsonPath('multimedia.0', "/api/content/multimedia/{$publicAsset->id}")
        ->assertJsonCount(1, 'multimedia')
        ->assertJsonMissing(["/api/content/multimedia/{$privateAsset->id}"]);
});

it('serves catalog items in the JSON:API envelope with relationships and includes', function (): void {
    $group = ProductCategoryFactory::new()->active()->create();
    $item = ProductFactory::new()->forCategory($group)->create(['in_stock' => true]);
    $price = ProductPriceFactory::new()->forProduct($item)->forCurrencyCode('USD')->create();

    $this->getJson("/api/catalog/products?inStock=1&categoryId={$group->id}&include=productPrices", ['Accept' => 'application/vnd.api+json'])
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.api+json; charset=utf-8')
        ->assertJsonPath('meta.totalItems', 1)
        ->assertJsonPath('data.0.type', 'Product')
        ->assertJsonPath('data.0.id', "/api/catalog/products/{$item->id}")
        ->assertJsonPath('data.0.attributes.id', $item->id)
        ->assertJsonPath('data.0.attributes.inStock', true)
        ->assertJsonPath('data.0.attributes.productCategory.id', $group->id)
        ->assertJsonPath('data.0.relationships.productPrices.data.0.id', "/api/catalog/product-prices/{$price->id}")
        ->assertJsonPath('included.0.attributes.amount', ProductPrice::toMajorUnits('USD', (int) $price->price->getAmount()))
        ->assertJsonPath('included.0.attributes.formatted', $price->formattedPrice());
});

it('looks up a product by its translatable slug and by token', function (): void {
    $group = ProductCategoryFactory::new()->active()->create();
    $locale = app()->getLocale();
    $item = ProductFactory::new()->forCategory($group)->create([
        'name' => [$locale => 'Red Rose'],
        'slug' => [$locale => 'red-rose'],
    ]);
    ProductFactory::new()->forCategory($group)->create([
        'name' => [$locale => 'Tulip'],
        'slug' => [$locale => 'tulip'],
    ]);
    $headers = [
        'Accept' => 'application/vnd.api+json',
        'Accept-Language' => $locale,
    ];

    $this->getJson('/api/catalog/products?slug=red-rose', $headers)
        ->assertOk()
        ->assertJsonPath('meta.totalItems', 1)
        ->assertJsonPath('data.0.attributes.id', $item->id);

    $this->getJson("/api/catalog/products?token={$item->token}", $headers)
        ->assertOk()
        ->assertJsonPath('meta.totalItems', 1)
        ->assertJsonPath('data.0.attributes.id', $item->id);

    $this->getJson('/api/catalog/products?search=rose', $headers)
        ->assertOk()
        ->assertJsonPath('meta.totalItems', 1)
        ->assertJsonPath('data.0.attributes.id', $item->id);
});

it('sorts catalog items with the API Platform order filter', function (string $direction, bool $descending): void {
    $group = ProductCategoryFactory::new()->active()->create();
    $firstItem = ProductFactory::new()->forCategory($group)->create();
    $secondItem = ProductFactory::new()->forCategory($group)->create();
    $expectedIds = $descending
        ? [$secondItem->id, $firstItem->id]
        : [$firstItem->id, $secondItem->id];

    $this->getJson("/api/catalog/products?sort[id]={$direction}", ['Accept' => 'application/vnd.api+json'])
        ->assertOk()
        ->assertJsonPath('data.0.attributes.id', Arr::get($expectedIds, 0))
        ->assertJsonPath('data.1.attributes.id', Arr::get($expectedIds, 1));
})->with([
    'newest first' => ['desc', true],
    'oldest first' => ['asc', false],
]);

it('validates and applies the random order filter', function (): void {
    $group = ProductCategoryFactory::new()->active()->create();
    ProductFactory::new()->forCategory($group)->create();

    $this->getJson('/api/catalog/products?random=1', ['Accept' => 'application/vnd.api+json'])
        ->assertOk()
        ->assertJsonPath('meta.totalItems', 1);

    $this->getJson('/api/catalog/products?random=invalid', ['Accept' => 'application/vnd.api+json'])
        ->assertUnprocessable();
});

it('exposes localized tiptap description documents on products and categories', function (): void {
    $document = [
        'type' => 'doc',
        'content' => [[
            'type' => 'paragraph',
            'content' => [['type' => 'text', 'text' => 'Hello']],
        ]],
    ];

    $category = ProductCategoryFactory::new()->active()->create();
    $category->setTranslations('description', ['en' => $document, 'fa' => $document])->save();

    $product = ProductFactory::new()->forCategory($category)->create();
    $product->setTranslations('description', ['en' => $document, 'fa' => $document])->save();

    $this->getJson("/api/catalog/products/{$product->id}", ['Accept' => 'application/ld+json'])
        ->assertOk()
        ->assertJsonPath('description.en', $document)
        ->assertJsonPath('description.fa.content.0.content.0.text', 'Hello');

    $this->getJson("/api/catalog/product-categories/{$category->id}", ['Accept' => 'application/ld+json'])
        ->assertOk()
        ->assertJsonPath('description.en', $document)
        ->assertJsonPath('description.fa', $document);
});
