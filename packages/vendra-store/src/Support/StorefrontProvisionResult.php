<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

/**
 * `ready` means the health gate passed; otherwise the deployment is recorded as
 * requested for reconciliation to revisit.
 */
final readonly class StorefrontProvisionResult
{
    public function __construct(
        public bool $ready,
        public ?string $reference = null,
        public ?string $imageDigest = null,
    ) {}

    /**
     * Create a result, treating a blank reference or digest as null.
     */
    public static function make(bool $ready, ?string $reference, ?string $imageDigest): self
    {
        return new self(
            ready: $ready,
            reference: self::filled($reference),
            imageDigest: self::filled($imageDigest),
        );
    }

    private static function filled(?string $value): ?string
    {
        return $value !== null && mb_trim($value) !== '' ? mb_trim($value) : null;
    }
}
