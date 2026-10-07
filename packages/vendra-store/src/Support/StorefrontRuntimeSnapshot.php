<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Misaf\VendraStore\Enums\StorefrontRuntimeState;

final readonly class StorefrontRuntimeSnapshot
{
    public function __construct(
        public CarbonImmutable $checkedAt,
        public ?StorefrontObservation $observation = null,
        public ?string $logs = null,
        public ?string $error = null,
    ) {}

    /** @return array{checked_at: string, observation: array{state: string, image: ?string, container_name: ?string, domain: ?string}|null, logs: ?string, error: ?string} */
    public function toArray(): array
    {
        return [
            'checked_at' => $this->checkedAt->toIso8601String(),
            'observation' => $this->observation === null ? null : [
                'state' => $this->observation->state->value,
                'image' => $this->observation->image,
                'container_name' => $this->observation->containerName,
                'domain' => $this->observation->domain,
            ],
            'logs' => $this->logs,
            'error' => $this->error,
        ];
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        $observation = Arr::get($data, 'observation');

        return new self(
            checkedAt: CarbonImmutable::parse(Arr::string($data, 'checked_at')),
            observation: is_array($observation) ? new StorefrontObservation(
                state: StorefrontRuntimeState::from(Arr::string($observation, 'state')),
                image: self::nullableString($observation, 'image'),
                containerName: self::nullableString($observation, 'container_name'),
                domain: self::nullableString($observation, 'domain'),
            ) : null,
            logs: self::nullableString($data, 'logs'),
            error: self::nullableString($data, 'error'),
        );
    }

    /** @param array<mixed> $data */
    private static function nullableString(array $data, string $key): ?string
    {
        $value = Arr::get($data, $key);

        return is_string($value) ? $value : null;
    }
}
