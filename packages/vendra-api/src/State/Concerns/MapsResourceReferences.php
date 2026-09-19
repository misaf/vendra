<?php

declare(strict_types=1);

namespace Misaf\VendraApi\State\Concerns;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraApi\ApiResource\ResourceReference;
use UnexpectedValueException;

trait MapsResourceReferences
{
    /**
     * Assert the mapper received the expected model.
     *
     * Accepts mixed so a missing relation throws the mapper's message, not a TypeError.
     *
     * @template T of Model
     *
     * @param  class-string<T>  $expected
     *
     * @phpstan-assert T $value
     */
    protected function expectModel(mixed $value, string $expected, string $message): void
    {
        throw_unless($value instanceof $expected, UnexpectedValueException::class, $message);
    }

    /**
     * Reference a related record, labeled with its name in the active locale.
     *
     * The label is dropped when the translation is missing or not a string.
     */
    protected function referenceTo(Model $related, string $type, string $labelAttribute = 'name'): ResourceReference
    {
        $label = method_exists($related, 'getTranslation')
            ? $related->getTranslation($labelAttribute, app()->getLocale())
            : $related->getAttribute($labelAttribute);

        return new ResourceReference(
            $related->getKey(),
            $type,
            is_string($label) ? $label : null,
        );
    }

    /**
     * @param  iterable<array-key, Model>  $related
     * @return list<ResourceReference>
     */
    protected function referencesTo(iterable $related, string $type, string $labelAttribute = 'name'): array
    {
        $references = [];

        foreach ($related as $model) {
            $references[] = $this->referenceTo($model, $type, $labelAttribute);
        }

        return $references;
    }
}
