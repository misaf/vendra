<?php

declare(strict_types=1);

namespace Misaf\VendraApi\Eloquent\Filter;

use ApiPlatform\Laravel\Eloquent\Filter\FilterInterface;
use ApiPlatform\Metadata\Parameter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class LocalizedSearchFilter implements FilterInterface
{
    /**
     * @param  Builder<Model>  $builder
     * @param array{
     *     properties?: array<string, bool>,
     *     whereClause?: 'where'|'orWhere'
     * } $context
     */
    public function apply(Builder $builder, mixed $values, Parameter $parameter, array $context = []): Builder
    {
        $properties = $context['properties'] ?? [];

        if (! is_string($values) || $values === '' || $properties === []) {
            return $builder;
        }

        $locale = app()->getLocale();
        $whereClause = $context['whereClause'] ?? 'where';

        return $builder->{$whereClause}(function (Builder $query) use ($locale, $properties, $values): void {
            foreach ($properties as $property => $localized) {
                $column = $localized ? "{$property}->{$locale}" : $property;
                $query->orWhere($column, 'like', "%{$values}%");
            }
        });
    }
}
