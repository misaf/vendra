<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Misaf\VendraStore\Enums\StorefrontRuntimeState;

final readonly class StorefrontObservation
{
    public function __construct(
        public StorefrontRuntimeState $state,
        public ?string $image = null,
        public ?string $containerName = null,
        public ?string $domain = null,
    ) {}

    /**
     * Create an observation from a container; no container means Absent.
     */
    public static function fromContainer(?StorefrontContainer $container): self
    {
        return new self(
            state: StorefrontRuntimeState::fromContainer($container),
            image: $container?->image,
            containerName: $container?->name,
            domain: $container?->labels[StorefrontContainerDefinitionFactory::DOMAIN_LABEL] ?? null,
        );
    }

    public function isAbsent(): bool
    {
        return $this->state === StorefrontRuntimeState::Absent;
    }

    /**
     * Determine if the runtime is serving an image other than the given one.
     *
     * Image references are compared because local image ids and registry digests
     * never match. An unobserved image is not drift.
     */
    public function isServingOtherThan(string $image): bool
    {
        return $this->image !== null && $this->image !== $image;
    }

    /**
     * Determine if the runtime is routing the storefront on a different domain.
     *
     * An unobserved domain, from a container older than the label, is not drift.
     */
    public function isServingDomainOtherThan(string $domain): bool
    {
        return $this->domain !== null && $this->domain !== $domain;
    }
}
