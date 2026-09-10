<?php

declare(strict_types=1);

namespace Misaf\VendraApi\Eloquent\Filter;

use ApiPlatform\Laravel\Eloquent\Filter\FilterInterface;
use ApiPlatform\Metadata\Parameter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class LocalizedEqualsFilter implements FilterInterface
{
    /**
     * @param  Builder<Model>  $builder
     * @param  array<string, mixed>  $context
     */
    public function apply(Builder $builder, mixed $values, Parameter $parameter, array $context = []): Builder
    {
        $property = $parameter->getProperty();

        if (! is_string($values) || $values === '' || ! is_string($property)) {
            return $builder;
        }

        return $builder->where("{$property}->".app()->getLocale(), $values);
    }
}
