<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use InvalidArgumentException;

final readonly class StorefrontContainerResources
{
    private const int BYTES_PER_MEGABYTE = 1024 * 1024;

    private const int NANO_CPUS = 1_000_000_000;

    public function __construct(
        public ?float $cpus = null,
        public ?int $memoryMegabytes = null,
        public ?int $memoryReservationMegabytes = null,
        public ?int $pidsLimit = null,
    ) {
        throw_if($cpus !== null && $cpus <= 0, InvalidArgumentException::class, 'A CPU limit must be greater than zero.');

        throw_if($memoryMegabytes !== null && $memoryMegabytes <= 0, InvalidArgumentException::class, 'A memory limit must be greater than zero.');

        throw_if($memoryReservationMegabytes !== null && $memoryReservationMegabytes <= 0, InvalidArgumentException::class, 'A memory reservation must be greater than zero.');

        throw_if($pidsLimit !== null && $pidsLimit <= 0, InvalidArgumentException::class, 'A PID limit must be greater than zero.');

        throw_if($memoryMegabytes !== null && $memoryReservationMegabytes !== null && $memoryReservationMegabytes > $memoryMegabytes, InvalidArgumentException::class, 'A memory reservation may not exceed the memory limit.');
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
