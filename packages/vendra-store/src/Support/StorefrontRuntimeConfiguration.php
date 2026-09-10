<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

final readonly class StorefrontRuntimeConfiguration
{
    public function __construct(
        public string $driver,
        public string $host,
    ) {}

    public static function fromConfig(): self
    {
        $driver = Config::string('container.default', 'docker');
        $host = Arr::get(Config::array("container.drivers.{$driver}"), 'host');

        return new self($driver, is_string($host) ? mb_trim($host) : '');
    }

    public function isConfigured(): bool
    {
        return $this->host !== '';
    }

    public function misconfigurationMessage(): string
    {
        return sprintf(
            'Container driver [%s] has no host configured. Set its host in container.drivers.%s.',
            $this->driver,
            $this->driver,
        );
    }
}
