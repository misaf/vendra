<?php

declare(strict_types=1);

namespace Misaf\VendraApi\State\Concerns;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraApi\ApiResource\ResourceReference;
use UnexpectedValueException;

/**
 * Turning a related model into a ResourceReference, and asserting that a mapper
 * was handed the model it expects.
 *
 * Every content mapper repeated both: the same instanceof-guard-and-throw, and
 * the same "read the related row's localized name, keep it only if it really is
 * a string" dance. Repeated by hand they drifted — the label attribute and the
 * null handling were re-decided per package.
 */
trait MapsResourceReferences
{
    /**
     * Asserts a mapper was handed what it expects.
     *
     * Takes mixed rather than Model deliberately: the common failure is an
     * absent relation, and null has to surface as the mapper's own message
     * rather than a TypeError from the guard itself.
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
     * A reference to a related record, labelled with its name in the active
     * locale. The label is dropped rather than coerced when the translation is
     * missing or is not a string.
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
