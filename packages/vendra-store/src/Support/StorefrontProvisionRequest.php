<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use InvalidArgumentException;
use JsonException;
use Misaf\VendraStore\Models\StorefrontDeployment;

/**
 * Everything a provisioner needs to place one storefront, as a typed value.
 *
 * The port used to speak in `array<string, mixed>`, which meant the real
 * contract lived in the adapter's key lookups and the caller re-validated every
 * field it got back. Assembling the request in one named place also keeps the
 * job out of the payload-building business: it moves a deployment through its
 * states and nothing more.
 */
final readonly class StorefrontProvisionRequest
{
    /**
     * @param  array<string, mixed>  $configuration  the configuration the storefront image boots on
     */
    public function __construct(
        public int $tenantId,
        public string $slug,
        public string $domain,
        public string $image,
        public array $configuration,
    ) {}

    /**
     * Build the request for a stored deployment.
     *
     * The identity fields are re-derived from the deployment row rather than
     * trusted from the stored configuration, so a container is never created for
     * a domain the deployment does not claim.
     */
    public static function for(StorefrontDeployment $deployment): self
    {
        $storefrontImage = $deployment->storefrontImage;

        throw_if($storefrontImage === null, InvalidArgumentException::class, 'Select a storefront image before deploying this storefront.');

        return new self(
            tenantId: $deployment->store_id,
            slug: $deployment->slug,
            domain: $deployment->domain,
            image: $storefrontImage->image,
            configuration: [
                'slug' => $deployment->slug,
                'domain' => $deployment->domain,
                'siteUrl' => 'https://'.$deployment->domain,
                ...$deployment->configuration,
            ],
        );
    }

    /**
     * The configuration as the container receives it: base64-encoded JSON.
     *
     * @throws JsonException
     */
    public function encodedConfiguration(): string
    {
        return base64_encode(json_encode(
            $this->configuration,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        ));
    }
}
