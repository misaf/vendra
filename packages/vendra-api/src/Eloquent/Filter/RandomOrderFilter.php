<?php

declare(strict_types=1);

namespace Misaf\VendraApi\Eloquent\Filter;

use ApiPlatform\Laravel\Eloquent\Filter\FilterInterface;
use ApiPlatform\Metadata\JsonSchemaFilterInterface;
use ApiPlatform\Metadata\Parameter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class RandomOrderFilter implements FilterInterface, JsonSchemaFilterInterface
{
    /**
     * @param  Builder<Model>  $builder
     * @param  array<string, mixed>  $context
     */
    public function apply(Builder $builder, mixed $values, Parameter $parameter, array $context = []): Builder
    {
        if (! in_array($values, [true, 1, '1', 'true'], true)) {
            return $builder;
        }

        return $builder->inRandomOrder();
    }

    /**
     * @return array{type: string}
     */
    public function getSchema(Parameter $parameter): array
    {
        return ['type' => 'boolean'];
    }
}
