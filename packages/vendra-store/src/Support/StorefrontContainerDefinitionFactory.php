<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use JsonException;

/**
 * Docker payload details stay in the runtime adapter.
 */
final readonly class StorefrontContainerDefinitionFactory
{
    /**
     * The label that marks a container as placed by the platform.
     *
     * Labels cannot change in place, so renaming this orphans every existing
     * container until the estate is destroyed and redeployed.
     */
    public const string MANAGED_BY_LABEL = 'io.vendra.managed-by';

    public const string MANAGED_BY = 'vendra';

    /**
     * The label holding the storefront's routed domain, read back by reconciliation.
     */
    public const string DOMAIN_LABEL = 'io.vendra.domain';

    public function __construct(private StorefrontSettings $settings) {}

    /**
     * @throws JsonException
     */
    public function build(StorefrontProvisionRequest $request): StorefrontContainerDefinition
    {
        $port = $this->settings->port;

        return new StorefrontContainerDefinition(
            name: $this->settings->containerName($request->slug),
            image: $request->image,
            environment: $this->environment($request),
            labels: $this->labels($request->slug, $request->domain, $port),
            port: $port,
            binds: $this->volumes(),
            network: $this->settings->network,
            healthCheck: $this->healthCheck($port),
            logDriver: $this->settings->logDriver,
            logOptions: $this->settings->logOptions,
            resources: $this->settings->resources,
            securityOptions: ['no-new-privileges:true'],
        );
    }

    /**
     * @return array<string, string>
     *
     * @throws JsonException
     */
    private function environment(StorefrontProvisionRequest $request): array
    {
        return [
            'NODE_ENV' => 'production',
            'STOREFRONT_CONFIG_BASE64' => $request->encodedConfiguration(),
            'VENDRA_API_URL' => $this->settings->resolvedApiUrl(),

            /*
             | When the estate terminates TLS with a certificate no public root
             | signs, Node rejects every server-side call to the API with
             | DEPTH_ZERO_SELF_SIGNED_CERT while the page still renders — the
             | failure shows only as empty sections. Empty whenever the system
             | roots already cover the API, which is the common case.
             */
            'NODE_EXTRA_CA_CERTS' => $this->settings->resolvedCaFile(),

            'STORAGE_BASE_URL' => $this->settings->storageBaseUrl,
            'NESHAN_SERVICE_KEY' => $this->settings->neshanServiceKey,
        ];
    }

    /**
     * Get the Traefik routing and ownership labels.
     *
     * The load balancer health check stops Traefik routing to an unhealthy container.
     *
     * @return array<string, string>
     */
    private function labels(string $slug, string $domain, int $port): array
    {
        $healthPath = $this->settings->healthPath;

        $labels = [
            'traefik.enable' => 'true',
            'traefik.docker.network' => $this->settings->network,

            "traefik.http.services.{$slug}.loadbalancer.server.port" => (string) $port,
            "traefik.http.services.{$slug}.loadbalancer.healthcheck.path" => $healthPath,
            "traefik.http.services.{$slug}.loadbalancer.healthcheck.interval" => '10s',
            "traefik.http.services.{$slug}.loadbalancer.healthcheck.timeout" => '3s',

            "traefik.http.routers.{$slug}.rule" => sprintf('Host(`%s`) || Host(`www.%s`)', $domain, $domain),
            "traefik.http.routers.{$slug}.entrypoints" => 'websecure',
            "traefik.http.routers.{$slug}.tls" => 'true',

            // Ownership markers, so the platform never touches another container.
            self::MANAGED_BY_LABEL => self::MANAGED_BY,
            'io.vendra.slug' => $slug,
            self::DOMAIN_LABEL => $domain,
        ];

        if ($this->settings->certResolver !== '') {
            $labels["traefik.http.routers.{$slug}.tls.certresolver"] = $this->settings->certResolver;
        }

        if ($this->settings->traefikMiddlewares !== '') {
            $labels["traefik.http.routers.{$slug}.middlewares"] = $this->settings->traefikMiddlewares;
        }

        return $labels;
    }

    /** @return list<string> */
    private function healthCheck(int $port): array
    {
        $probe = sprintf(
            "fetch('http://127.0.0.1:%d%s').then(r=>{if(!r.ok)process.exit(1)}).catch(()=>process.exit(1))",
            $port,
            $this->settings->healthPath,
        );

        return ['CMD', 'node', '-e', $probe];
    }

    /**
     * @return list<string>
     */
    private function volumes(): array
    {
        $certificates = $this->settings->certificatesPath;

        return $certificates === '' ? [] : [sprintf('%s:/certs:ro', $certificates)];
    }
}
