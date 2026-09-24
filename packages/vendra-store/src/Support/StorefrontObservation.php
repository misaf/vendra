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
        /** @var list<string>|null */
        public ?array $aliases = null,
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
            aliases: self::aliasesFrom($container?->labels[StorefrontContainerDefinitionFactory::ALIASES_LABEL] ?? null),
        );
    }

    /**
     * @return list<string>|null
     */
    private static function aliasesFrom(?string $label): ?array
    {
        if ($label === null) {
            return null;
        }

        return array_values(array_filter(explode(',', $label), fn (string $alias): bool => $alias !== ''));
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

    /**
     * Determine if the runtime is routing a different set of alias domains.
     *
     * Unobserved aliases, from a container older than the label, are not drift.
     *
     * @param  list<string>  $aliases
     */
    public function isServingAliasesOtherThan(array $aliases): bool
    {
        if ($this->aliases === null) {
            return false;
        }

        $observed = $this->aliases;
        sort($observed);
        sort($aliases);

        return $observed !== $aliases;
    }
}
