<?php

declare(strict_types=1);

namespace Misaf\VendraApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Laravel\Eloquent\State\CollectionProvider;
use ApiPlatform\Laravel\Eloquent\State\ItemProvider;
use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\ProviderInterface;
use Generator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use RuntimeException;

/**
 * Single generic read provider for every Eloquent-backed API resource.
 *
 * Delegates fetching to the built-in Eloquent item/collection providers, then
 * maps each model to its resource through the {@see ResourceMapper} declared on
 * the operation's {@see EloquentResourceOptions}. Query scoping stays in the
 * resource's own {@see LinksHandlerInterface}.
 *
 * Collections are re-wrapped as an Eloquent {@see Paginator} (not a
 * TraversablePaginator) so ApiPlatform's access checker resolves the operation
 * policy against the resource rather than the paginator object.
 *
 * @implements ProviderInterface<object>
 */
final class EloquentResourceProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $mapper = $this->resolveMapper($operation);
        $modelClass = $this->resolveModelClass($operation);

        if ($operation instanceof CollectionOperationInterface) {
            $models = resolve(CollectionProvider::class)->provide($operation, $uriVariables, $context);

            if ($models instanceof PaginatorInterface) {
                return new Paginator(new LengthAwarePaginator(
                    iterator_to_array($this->mapModels($models, $mapper, $modelClass), false),
                    (int) $models->getTotalItems(),
                    (int) $models->getItemsPerPage(),
                    (int) $models->getCurrentPage(),
                ));
            }

            return is_iterable($models)
                ? iterator_to_array($this->mapModels($models, $mapper, $modelClass), false)
                : [];
        }

        $model = resolve(ItemProvider::class)->provide($operation, $uriVariables, $context);

        return $model instanceof $modelClass ? $mapper->map($model) : null;
    }

    /**
     * @param  iterable<object>  $models
     * @param  class-string<Model>  $modelClass
     * @return Generator<int, object>
     */
    private function mapModels(iterable $models, ResourceMapper $mapper, string $modelClass): Generator
    {
        foreach ($models as $model) {
            if ($model instanceof $modelClass) {
                yield $mapper->map($model);
            }
        }
    }

    private function resolveMapper(Operation $operation): ResourceMapper
    {
        $options = $operation->getStateOptions();
        $mapperClass = $options instanceof EloquentResourceOptions ? $options->getMapper() : null;

        if ($mapperClass === null) {
            throw new RuntimeException(sprintf(
                'Operation "%s" must declare an %s stateOptions with a mapper.',
                $operation->getName() ?? $operation->getShortName() ?? 'unknown',
                EloquentResourceOptions::class,
            ));
        }

        $mapper = resolve($mapperClass);

        if (! $mapper instanceof ResourceMapper) {
            throw new RuntimeException(sprintf(
                'The mapper "%s" must implement %s.',
                $mapperClass,
                ResourceMapper::class,
            ));
        }

        return $mapper;
    }

    /**
     * @return class-string<Model>
     */
    private function resolveModelClass(Operation $operation): string
    {
        $options = $operation->getStateOptions();
        $modelClass = $options instanceof EloquentResourceOptions ? $options->getModelClass() : null;

        if ($modelClass === null || ! is_subclass_of($modelClass, Model::class)) {
            throw new RuntimeException(sprintf(
                'Operation "%s" must declare an Eloquent model class in its stateOptions.',
                $operation->getName() ?? $operation->getShortName() ?? 'unknown',
            ));
        }

        return $modelClass;
    }
}
