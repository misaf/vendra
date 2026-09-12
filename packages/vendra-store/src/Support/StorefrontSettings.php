<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

/**
 * The `vendra-store.storefront` block as an immutable, typed value.
 *
 * Every consumer used to reach into `Config` and re-implement the same
 * string/int/bool coercion, so the shape of the block was described three times
 * and enforced nowhere. Reading it once, here, means the deployment layer and
 * the container-definition factory receive settled values instead of an untyped
 * array and a private accessor triplet each.
 *
 * Note what is *not* here: the runtime endpoint and its API version. Those are
 * Laravel Docker Engine driver settings, and a storefront setting does not know
 * what a socket is.
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
     * Bound as a non-singleton so a configuration change — a test setting an
     * image, an administrator reloading config — is picked up on the next resolve
     * rather than frozen at first use.
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

    /**
     * The API origin storefront containers call, explicit or derived.
     */
    public function resolvedApiUrl(): string
    {
        if ($this->apiUrl !== '') {
            return $this->apiUrl;
        }

        return $this->baseDomain === '' ? '' : 'https://api.'.$this->baseDomain;
    }

    /**
     * The CA bundle path inside the container.
     *
     * Resolved against the read-only certificate mount, so administrators configure a
     * file name rather than an in-container path they cannot see.
     */
    public function resolvedCaFile(): string
    {
        if ($this->caFile === '') {
            return '';
        }

        return str_starts_with($this->caFile, '/') ? $this->caFile : '/certs/'.$this->caFile;
    }

    /**
     * The fleet's per-storefront caps.
     *
     * A zero, a blank, or a missing key lifts that cap rather than setting it
     * to nothing: the administrator-facing way to say "uncapped" is to empty the
     * environment variable.
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
