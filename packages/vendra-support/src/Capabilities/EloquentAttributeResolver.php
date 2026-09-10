<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Capabilities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Contracts\AttributeResolver;
use Throwable;

final class EloquentAttributeResolver implements AttributeResolver
{
    /**
     * @param  class-string<Model>  $attributeModel
     * @param  class-string<Model>  $attributeValueModel
     */
    public function __construct(
        private readonly string $attributeModel,
        private readonly string $attributeValueModel,
        private readonly AttributeResolver $fallback = new NullAttributeResolver,
        private readonly string $nameColumn = 'name',
        private readonly string $unitColumn = 'unit',
        private readonly string $activeColumn = 'active',
        private readonly string $positionColumn = 'position',
    ) {}

    public function available(): bool
    {
        return true;
    }

    public function valueModel(): string
    {
        return $this->attributeValueModel;
    }

    /** @return array<int|string, string> */
    public function options(): array
    {
        try {
            $options = $this->query()
                ->where($this->activeColumn, true)
                ->orderBy($this->positionColumn, 'desc')
                ->get()
                ->mapWithKeys(function (Model $attribute): array {
                    $key = $attribute->getKey();
                    $name = $attribute->getAttribute($this->nameColumn);
                    $unit = $attribute->getAttribute($this->unitColumn);

                    if ((! is_int($key) && ! is_string($key)) || ! is_string($name) || $name === '') {
                        return [];
                    }

                    return [$key => is_string($unit) && $unit !== '' ? "{$name} ({$unit})" : $name];
                })
                ->all();

            return $options !== [] ? $options : $this->fallback->options();
        } catch (Throwable) {
            return $this->fallback->options();
        }
    }

    /** @return Builder<Model> */
    private function query(): Builder
    {
        return (new $this->attributeModel)->newQuery();
    }
}
