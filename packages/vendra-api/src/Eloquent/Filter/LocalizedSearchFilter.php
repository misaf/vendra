<?php

declare(strict_types=1);

namespace Misaf\VendraApi\Eloquent\Filter;

use ApiPlatform\Laravel\Eloquent\Filter\FilterInterface;
use ApiPlatform\Metadata\Parameter;
use Illuminate\Contracts\Database\Query\ConditionExpression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Grammar;
use Illuminate\Support\Arr;

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
        $properties = Arr::array($context, 'properties', []);

        if (! is_string($values) || $values === '' || $properties === []) {
            return $builder;
        }

        $locale = app()->getLocale();
        $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($values)).'%';

        $search = function (Builder $query) use ($locale, $pattern, $properties): void {
            foreach ($properties as $property => $localized) {
                if (! is_string($property)) {
                    continue;
                }

                // A condition expression adds no bindings of its own, so the pattern follows it directly.
                $query->orWhere(self::lowercaseLike($localized === true ? "{$property}->{$locale}" : $property));
                $query->getQuery()->addBinding($pattern, 'where');
            }
        };

        return Arr::get($context, 'whereClause') === 'orWhere' ? $builder->orWhere($search) : $builder->where($search);
    }

    /**
     * Compare case-insensitively and treat `%` and `_` in the term literally.
     *
     * MySQL compares extracted JSON strings with a binary collation, so both sides are
     * lowercased; the explicit `ESCAPE '!'` is portable where the default escape is not.
     */
    private static function lowercaseLike(string $column): ConditionExpression
    {
        return new readonly class($column) implements ConditionExpression
        {
            public function __construct(private string $column) {}

            public function getValue(Grammar $grammar): string
            {
                return 'lower('.$grammar->wrap($this->column).") like ? escape '!'";
            }
        };
    }
}
