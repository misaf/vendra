<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use InvalidArgumentException;
use JsonException;
use Misaf\VendraStore\Models\StorefrontDeployment;

final readonly class StorefrontProvisionRequest
{
    /**
     * @param  array<string, mixed>  $configuration  the configuration the storefront image boots on
     */
    public function __construct(
        public string $slug,
        public string $domain,
        public string $image,
        public array $configuration,
    ) {}

    /**
     * Build the request for a deployment.
     *
     * Identity fields come from the deployment row, never the stored configuration.
     */
    public static function for(StorefrontDeployment $deployment): self
    {
        $storefrontImage = $deployment->storefrontImage;

        throw_if($storefrontImage === null, InvalidArgumentException::class, 'Select a storefront image before deploying this storefront.');

        return new self(
            slug: $deployment->slug,
            domain: $deployment->domain,
            image: $storefrontImage->image,
            configuration: [
                ...$deployment->configuration,
                'slug' => $deployment->slug,
                'domain' => $deployment->domain,
                'siteUrl' => 'https://'.$deployment->domain,
            ],
        );
    }

    /**
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
