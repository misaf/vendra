<?php

declare(strict_types=1);

namespace Misaf\VendraLocalization\Contracts;

interface ProvidesVaryHeaders
{
    /**
     * Get the request headers to add to the response's `Vary` header.
     *
     * @return list<string>
     */
    public function varyHeaders(): array;
}
