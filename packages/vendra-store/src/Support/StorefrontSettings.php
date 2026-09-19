<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

/**
 * The runtime endpoint belongs to the Docker Engine driver config, not here.
 */
final readonly class StorefrontSettings
{
    /**
     * @param  array<string, string>  $logOptions
     */
    public function __construct(
        public string $network,
        public string $namePrefix,
        public int $port,
        public string $healthPath,
        public int $healthTimeout,
        public bool $pull,
        public string $logDriver,
        public array $logOptions,
        public StorefrontContainerResources $resources,
        public string $baseDomain,
        public string $apiUrl,
        public string $certResolver,
        public string $certificatesPath,
        public string $caFile,
        public string $storageBaseUrl,
        public string $neshanServiceKey,
        public string $traefikMiddlewares,
    ) {}

    /**
     * Read the current configuration.
     *
     * Not a singleton, so config changes are picked up on the next resolve.
     */
    public static function fromConfig(): self
    {
        $storefront = Config::array('vendra-store.storefront');

        return new self(
            network: self::string($storefront, 'network', 'traefik-public'),
            namePrefix: self::string($storefront, 'name_prefix', 'vendra-storefront-'),
            port: self::integer($storefront, 'port', 3000),
            healthPath: self::string($storefront, 'health_path', '/api/health'),
            healthTimeout: self::integer($storefront, 'health_timeout', 120),
            pull: filter_var(Arr::get($storefront, 'pull'), FILTER_VALIDATE_BOOL),
            logDriver: self::string($storefront, 'log_driver'),
            logOptions: self::logOptions($storefront),
            resources: self::resources($storefront),
            baseDomain: self::string($storefront, 'base_domain'),
            apiUrl: self::string($storefront, 'api_url'),
            certResolver: self::string($storefront, 'cert_resolver'),
            certificatesPath: self::string($storefront, 'certificates_path'),
            caFile: self::string($storefront, 'ca_file'),
            storageBaseUrl: self::string($storefront, 'storage_base_url'),
            neshanServiceKey: self::string($storefront, 'neshan_service_key'),
            traefikMiddlewares: self::string($storefront, 'traefik_middlewares'),
        );
    }

    public function containerName(string $slug): string
    {
        return $this->namePrefix.$slug;
    }

    public function resolvedApiUrl(): string
    {
        if ($this->apiUrl !== '') {
            return $this->apiUrl;
        }

        return $this->baseDomain === '' ? '' : 'https://api.'.$this->baseDomain;
    }

    /**
     * Get the CA bundle path inside the container's certificate mount.
     */
    public function resolvedCaFile(): string
    {
        if ($this->caFile === '') {
            return '';
        }

        return str_starts_with($this->caFile, '/') ? $this->caFile : '/certs/'.$this->caFile;
    }

    /**
     * Get the per-storefront resource caps; zero, blank, or missing means uncapped.
     *
     * @param  array<array-key, mixed>  $storefront
     */
    private static function resources(array $storefront): StorefrontContainerResources
    {
        $cpus = Arr::get($storefront, 'cpus');
        $cpus = is_numeric($cpus) && (float) $cpus > 0 ? (float) $cpus : null;

        return new StorefrontContainerResources(
            cpus: $cpus,
            memoryMegabytes: self::optionalPositiveInteger($storefront, 'memory_megabytes'),
            memoryReservationMegabytes: self::optionalPositiveInteger($storefront, 'memory_reservation_megabytes'),
            pidsLimit: self::optionalPositiveInteger($storefront, 'pids_limit'),
        );
    }

    /**
     * @param  array<array-key, mixed>  $storefront
     */
    private static function optionalPositiveInteger(array $storefront, string $key): ?int
    {
        $value = Arr::get($storefront, $key);

        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }

    /**
     * @param  array<array-key, mixed>  $storefront
     * @return array<string, string>
     */
    private static function logOptions(array $storefront): array
    {
        $options = Arr::get($storefront, 'log_options');
        $resolved = [];

        foreach (is_array($options) ? $options : [] as $key => $value) {
            if (is_string($key) && is_scalar($value) && (string) $value !== '') {
                $resolved[$key] = (string) $value;
            }
        }

        return $resolved;
    }

    /**
     * @param  array<array-key, mixed>  $storefront
     */
    private static function string(array $storefront, string $key, string $default = ''): string
    {
        $value = Arr::get($storefront, $key);

        return is_string($value) && mb_trim($value) !== '' ? mb_trim($value) : $default;
    }

    /**
     * @param  array<array-key, mixed>  $storefront
     */
    private static function integer(array $storefront, string $key, int $default): int
    {
        $value = Arr::get($storefront, $key);

        return is_numeric($value) && (int) $value > 0 ? (int) $value : $default;
    }
}
