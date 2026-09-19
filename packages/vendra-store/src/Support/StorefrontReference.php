<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use InvalidArgumentException;
use Misaf\VendraStore\Models\StorefrontDeployment;

/**
 * Identify a storefront by its slug, from which the container name derives.
 */
final readonly class StorefrontReference
{
    public function __construct(public string $slug)
    {
        throw_if(mb_trim($slug) === '', InvalidArgumentException::class, 'A storefront slug is required.');
    }

    public static function for(StorefrontDeployment $deployment): self
    {
        return new self($deployment->slug);
    }
}
