<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Illuminate\Support\Arr;

final class StorefrontContainer
{
    /** @param array<string, string> $labels */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $state,
        public readonly ?string $health,
        public readonly ?int $exitCode,
        public readonly ?string $image,
        public readonly array $labels,
    ) {}

    /** @param array<array-key, mixed> $payload */
    public static function fromEnginePayload(array $payload): self
    {
        $id = self::string($payload, 'Id');
        $name = mb_ltrim(self::string($payload, 'Name') ?? '', '/');
        $exitCode = Arr::get($payload, 'State.ExitCode');

        return new self(
            id: $id ?? $name,
            name: $name,
            state: mb_strtolower(self::string($payload, 'State.Status') ?? 'unknown'),
            health: self::string($payload, 'State.Health.Status'),
            exitCode: is_numeric($exitCode) ? (int) $exitCode : null,
            image: self::string($payload, 'Config.Image'),
            labels: self::labels($payload),
        );
    }

    public function isRunning(): bool
    {
        return $this->state === 'running';
    }

    public function hasStopped(): bool
    {
        return in_array($this->state, ['exited', 'dead'], true);
    }

    public function hasLabel(string $name, ?string $value = null): bool
    {
        return array_key_exists($name, $this->labels)
            && ($value === null || $this->labels[$name] === $value);
    }

    /**
     * @param  array<array-key, mixed>  $payload
     * @return array<string, string>
     */
    private static function labels(array $payload): array
    {
        $labels = [];

        foreach ((array) Arr::get($payload, 'Config.Labels', []) as $name => $value) {
            if (is_string($name) && is_string($value)) {
                $labels[$name] = $value;
            }
        }

        return $labels;
    }

    /** @param array<array-key, mixed> $payload */
    private static function string(array $payload, string $key): ?string
    {
        $value = Arr::get($payload, $key);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
