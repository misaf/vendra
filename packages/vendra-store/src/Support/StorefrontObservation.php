<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Misaf\VendraStore\Enums\StorefrontRuntimeState;

/**
 * What the runtime actually has for one storefront, right now.
 *
 * The port used to answer this with a bare state enum, so the only question
 * reconciliation could ask was "is it up?" — and the only way to be sure of
 * anything else was to redeploy. Carrying the image reference alongside the
 * state is what lets a converge pass tell a storefront serving the current
 * release from one still running the previous image.
 */
final class StorefrontObservation
{
    public function __construct(
        public readonly StorefrontRuntimeState $state,
        public readonly ?string $image = null,
        public readonly ?string $containerName = null,
        public readonly ?string $domain = null,
    ) {}

    /**
     * A null container is Absent rather than an error: "there is nothing there"
     * is the honest answer before a first deployment.
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
     * Whether the runtime is serving something other than the given image.
     *
     * Compared as *references* — the string the container was created with
     * against the one settings now name — because those are the only two values
     * here that are comparable at all. A container's `Image` is the runtime's
     * local image id and a deployment's recorded `image_digest` is the registry's
     * repo digest; they never match, so diffing those would report drift on every
     * storefront on every pass and redeploy the whole estate.
     *
     * An unobserved image is not drift: nothing was learned, so nothing changes.
     */
    public function isServingOtherThan(string $image): bool
    {
        return $this->image !== null && $this->image !== $image;
    }

    /**
     * Whether the runtime is routing this storefront on some other domain.
     *
     * The same shape as the image check, and for the same reason: a container's
     * labels are fixed at creation, so a store that changed domain leaves one
     * still carrying a `Host()` rule for the old one — serving the previous
     * address and nothing at the new one. Without this, a converge pass sees a
     * healthy container running the right image and calls it in sync.
     *
     * An unobserved domain is not drift: a container placed before this label
     * existed reports nothing, and rebuilding the estate over a missing label is
     * a worse answer than leaving it alone.
     */
    public function isServingDomainOtherThan(string $domain): bool
    {
        return $this->domain !== null && $this->domain !== $domain;
    }
}
