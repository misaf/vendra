<?php

declare(strict_types=1);

namespace Misaf\VendraApi\State;

use ApiPlatform\Laravel\Eloquent\State\Options;

/**
 * Eloquent state options extended with the {@see ResourceMapper} that
 * {@see EloquentResourceProvider} uses to turn models into resources.
 */
final class EloquentResourceOptions extends Options
{
    /**
     * @param  class-string<ResourceMapper>|null  $mapper
     */
    public function __construct(
        ?string $modelClass = null,
        mixed $handleLinks = null,
        private readonly ?string $mapper = null,
    ) {
        parent::__construct($modelClass, $handleLinks);
    }

    /**
     * @return class-string<ResourceMapper>|null
     */
    public function getMapper(): ?string
    {
        return $this->mapper;
    }
}
