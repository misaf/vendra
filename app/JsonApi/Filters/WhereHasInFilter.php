<?php

declare(strict_types=1);

namespace App\JsonApi\Filters;

use Closure;
use LaravelJsonApi\Core\Support\Str;
use LaravelJsonApi\Eloquent\Contracts\Filter;
use LaravelJsonApi\Eloquent\Filters\Concerns\DeserializesValue;
use LaravelJsonApi\Eloquent\Filters\Concerns\HasColumn;
use LaravelJsonApi\Eloquent\Filters\Concerns\HasDelimiter;
use LaravelJsonApi\Eloquent\Filters\Concerns\HasRelation;
use LaravelJsonApi\Eloquent\Filters\Concerns\IsSingular;
use LaravelJsonApi\Eloquent\Schema;

final class WhereHasInFilter implements Filter
{
    use DeserializesValue;
    use HasColumn;
    use HasDelimiter;
    use HasRelation;
    use IsSingular;

    /**
     * WhereHas constructor.
     *
     * @param Schema $schema
     * @param string $fieldName
     * @param string|null $key
     * @param string|null $column
     */
    public function __construct(Schema $schema, string $fieldName, ?string $key = null, ?string $column = null)
    {
        $this->schema = $schema;
        $this->fieldName = $fieldName;
        $this->key = $key;
        $this->column = $column ?: $this->guessColumn();
    }

    /**
     * Create a new filter.
     *
     * @param Schema $schema
     * @param string $fieldName
     * @param string|null $key
     * @param string|null $column
     * @return static
     */
    public static function make(Schema $schema, string $fieldName, ?string $key = null, ?string $column = null)
    {
        return new static($schema, $fieldName, $key, $column);
    }

    /**
     * @inheritDoc
     */
    public function apply($query, $value)
    {
        return $query->whereHas(
            $this->relationName(),
            $this->callback($value),
        );
    }

    /**
     * Get the relation query callback.
     *
     * @param mixed $value
     * @return Closure
     */
    protected function callback($value): Closure
    {
        return function ($query) use ($value): void {
            $query->whereIn(
                $query->getModel()->qualifyColumn($this->column()),
                $this->toArray(collect($value)->first()),
            );
        };
    }

    /**
     * @return string
     */
    private function guessColumn(): string
    {
        return Str::underscore(
            Str::singular($this->key()),
        );
    }
}
