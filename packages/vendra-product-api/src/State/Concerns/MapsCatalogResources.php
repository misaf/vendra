<?php

declare(strict_types=1);

namespace Misaf\VendraProductApi\State\Concerns;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraApi\ApiResource\ResourceReference;
use Misaf\VendraApi\State\Concerns\MapsResourceReferences;
use Misaf\VendraApi\State\Concerns\NormalizesResourceValues;
use Misaf\VendraMultimediaApi\ApiResource\MultimediaResource;
use Misaf\VendraMultimediaApi\State\MultimediaResourceFactory;
use Misaf\VendraMultimediaApi\State\PublicMultimedia;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraProductApi\ApiResource\ProductCategoryResource;
use Misaf\VendraProductApi\ApiResource\ProductPriceResource;
use Misaf\VendraProductApi\ApiResource\ProductResource;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;
use UnexpectedValueException;

/**
 * Shared mapping between catalog Eloquent models and their API resources.
 *
 * Catalog resources are compositionally nested — a product embeds its prices
 * and a category reference, a category embeds product references — so the
 * per-resource providers share one mapper rather than duplicating logic.
 */
trait MapsCatalogResources
{
    use MapsResourceReferences;
    use NormalizesResourceValues;

    protected function toProductResource(Product $product): ProductResource
    {
        return new ProductResource(
            id: $product->id,
            name: $this->normalizeTranslations($product->getTranslations('name')),
            slug: $this->normalizeTranslations($product->getTranslations('slug')),
            description: $this->normalizeTranslationDocuments($product->getTranslations('description')),
            token: $product->token,
            quantity: $product->quantity,
            inStock: $product->in_stock,
            stockThreshold: $product->stock_threshold ?? 0,
            position: $product->position ?? 0,
            availableSoon: $product->available_soon,
            availabilityDate: $product->availability_date?->toAtomString(),
            productCategory: $this->categoryReference($product->productCategory),
            productPrices: $product->productPrices
                ->map(fn (ProductPrice $price): ProductPriceResource => $this->toPriceResource($price, $product))
                ->all(),
            latestProductPrice: $product->latestProductPrice instanceof ProductPrice
                ? $this->toPriceResource($product->latestProductPrice, $product)
                : null,
            multimedia: $product->multimedia
                ->filter(fn (Model $media): bool => PublicMultimedia::isPublic($media))
                ->map(fn (Model $media): MultimediaResource => $this->toMultimediaResource($media))
                ->values()
                ->all(),
            options: $this->attributeReferences($product),
            createdAt: $product->created_at->toAtomString(),
            updatedAt: $product->updated_at->toAtomString(),
        );
    }

    protected function toCategoryResource(ProductCategory $category): ProductCategoryResource
    {
        return new ProductCategoryResource(
            id: $category->id,
            name: $this->normalizeTranslations($category->getTranslations('name')),
            slug: $this->normalizeTranslations($category->getTranslations('slug')),
            description: $this->normalizeTranslationDocuments($category->getTranslations('description')),
            position: $category->position,
            active: $category->active,
            products: $category->products
                ->map(fn (Product $product): ResourceReference => $this->productReference($product))
                ->all(),
            multimedia: $category->relationLoaded('multimedia')
                ? $category->multimedia
                    ->filter(fn (Model $media): bool => PublicMultimedia::isPublic($media))
                    ->map(fn (Model $media): MultimediaResource => $this->toMultimediaResource($media))
                    ->values()
                    ->all()
                : [],
            createdAt: $category->created_at->toAtomString(),
            updatedAt: $category->updated_at->toAtomString(),
        );
    }

    protected function toPriceResource(ProductPrice $price, ?Product $product = null): ProductPriceResource
    {
        $product ??= $price->product;

        return new ProductPriceResource(
            id: $price->id,
            minorAmount: (int) $price->price->getAmount(),
            amount: ProductPrice::toMajorUnits($price->currency_code, (int) $price->price->getAmount()),
            currency: $price->currency_code,
            formatted: $price->formattedPrice(),
            product: $this->productReference($product),
        );
    }

    protected function toMultimediaResource(Model $media): MultimediaResource
    {
        return MultimediaResourceFactory::make($media);
    }

    protected function productReference(?Product $product): ResourceReference
    {
        $this->expectModel($product, Product::class, 'A product price must belong to a product.');

        return $this->referenceTo($product, 'Product');
    }

    protected function categoryReference(?ProductCategory $category): ResourceReference
    {
        $this->expectModel($category, ProductCategory::class, 'A product must belong to a category.');

        return $this->referenceTo($category, 'ProductCategory');
    }

    /**
     * @return array<int, string>
     */
    protected function attributeRelations(): array
    {
        return AttributeIntegration::isAvailable() ? ['selectedAttributeValues'] : [];
    }

    /**
     * @return array<int, ResourceReference>
     */
    protected function attributeReferences(Product $product): array
    {
        if (! AttributeIntegration::isAvailable()) {
            return [];
        }

        return $product->selectedAttributeValues
            ->map(function (Model $value): ResourceReference {
                $identifier = $value->getKey();
                $label = $value->getAttribute('value');

                if (! is_int($identifier) && ! is_string($identifier)) {
                    throw new UnexpectedValueException('Attribute value identifiers must be integers or strings.');
                }

                return new ResourceReference(
                    $identifier,
                    'AttributeValue',
                    is_string($label) ? $label : null,
                );
            })
            ->all();
    }
}
