<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use InvalidArgumentException;

final class StorefrontContainerResources
{
    private const int BYTES_PER_MEGABYTE = 1024 * 1024;

    private const int NANO_CPUS = 1_000_000_000;

    public function __construct(
        public readonly ?float $cpus = null,
        public readonly ?int $memoryMegabytes = null,
        public readonly ?int $memoryReservationMegabytes = null,
        public readonly ?int $pidsLimit = null,
    ) {
        if ($cpus !== null && $cpus <= 0) {
            throw new InvalidArgumentException('A CPU limit must be greater than zero.');
        }

        if ($memoryMegabytes !== null && $memoryMegabytes <= 0) {
            throw new InvalidArgumentException('A memory limit must be greater than zero.');
        }

        if ($memoryReservationMegabytes !== null && $memoryReservationMegabytes <= 0) {
            throw new InvalidArgumentException('A memory reservation must be greater than zero.');
        }

        if ($pidsLimit !== null && $pidsLimit <= 0) {
            throw new InvalidArgumentException('A PID limit must be greater than zero.');
        }

        if ($memoryMegabytes !== null && $memoryReservationMegabytes !== null && $memoryReservationMegabytes > $memoryMegabytes) {
            throw new InvalidArgumentException('A memory reservation may not exceed the memory limit.');
        }
    }

    /** @return array<string, int> */
    public function enginePayload(): array
    {
        return array_filter([
            'NanoCpus' => $this->cpus === null ? null : (int) round($this->cpus * self::NANO_CPUS),
            'Memory' => $this->memoryMegabytes === null ? null : $this->memoryMegabytes * self::BYTES_PER_MEGABYTE,
            'MemoryReservation' => $this->memoryReservationMegabytes === null ? null : $this->memoryReservationMegabytes * self::BYTES_PER_MEGABYTE,
            'PidsLimit' => $this->pidsLimit,
        ], static fn (?int $value): bool => $value !== null);
    }
}
