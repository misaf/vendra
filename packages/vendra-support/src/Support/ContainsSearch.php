<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Support;

use Illuminate\Contracts\Database\Query\ConditionExpression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Grammar;

/**
 * Match a search term anywhere in any of the given columns, treating `%` and `_` in
 * the term literally.
 *
 * The explicit `ESCAPE '!'` clause keeps the escaping portable: MySQL and PostgreSQL
 * default to a backslash escape, while SQLite has none. Column names are quoted by
 * the connection's grammar, so a name read from a model at runtime is safe to pass.
 */
final class ContainsSearch
{
    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  list<string>  $columns
     * @return Builder<TModel>
     */
    public static function apply(Builder $query, array $columns, string $term): Builder
    {
        $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($term)).'%';

        return $query->where(function (Builder $query) use ($columns, $pattern): void {
            foreach ($columns as $column) {
                // A condition expression adds no bindings of its own, so the pattern follows it directly.
                $query->orWhere(self::lowercaseLike($column));
                $query->getQuery()->addBinding($pattern, 'where');
            }
        });
    }

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
