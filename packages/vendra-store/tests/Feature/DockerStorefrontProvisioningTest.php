<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Misaf\DockerEngine\Transport\Request;
use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Services\ContainerStorefrontProvisioner;
use Misaf\VendraStore\Support\StorefrontProvisionRequest;

/**
 * @param  array<string, mixed>  $overrides
 */
function storefrontConfiguration(array $overrides = []): array
{
    return [
        'slug' => 'acme-flowers',
        'domain' => 'acme.test',
        'siteUrl' => 'https://acme.test',
        'businessType' => 'Florist',
        'priceCurrency' => 'IRR',
        'name' => ['en' => 'Acme Flowers'],
        'address' => ['locality' => 'Tehran', 'country' => 'IR'],
        'contact' => [
            'mobilePhone' => '09120000000',
            'officePhone' => '02100000000',
            'email' => 'contact@acme.test',
            'hoursOpen' => '08:00',
            'hoursClose' => '21:00',
            'mapQuery' => '35.7,51.4',
        ],
        'social' => [
            'whatsappPhone' => '+989120000000',
            'telegramUsername' => 'acmeflowers',
            'instagramUsername' => 'acmeflowers',
        ],
        ...$overrides,
    ];
}

/**
 * @param  array<string, mixed>  $overrides
 */
function storefrontRequest(array $overrides = []): StorefrontProvisionRequest
{
    return new StorefrontProvisionRequest(
        tenantId: 1,
        slug: Arr::get($overrides, 'slug', 'acme-flowers'),
        domain: Arr::get($overrides, 'domain', 'acme.test'),
        image: Arr::get($overrides, 'image', 'ghcr.io/misaf/vendra-storefront-florist@sha256:abc123'),
        configuration: Arr::get($overrides, 'configuration', storefrontConfiguration()),
    );
}

/**
 * Run the job the way the queue does: dependencies resolved from the container.
 */
function runProvisionJob(StorefrontDeployment $deployment, bool $force = false): void
{
    app()->call([new ProvisionStorefrontJob($deployment->id, $force), 'handle']);
}

beforeEach(function (): void {
    Config::set('container.drivers.docker.host', 'http://provisioner:8080');
    Config::set('vendra-store.storefront.network', 'traefik-public');
    Config::set('vendra-store.storefront.pull', true);
    Config::set('vendra-store.storefront.base_domain', 'vendra.test');
    Config::set('vendra-store.storefront.cert_resolver', 'letsencrypt');
    Config::set('vendra-store.storefront.certificates_path', '/var/lib/vendra/certificates');
    Config::set('vendra-store.storefront.ca_file', 'vendra-ca.crt');
});

it('resolves the docker provisioner', function (): void {
    expect(resolve(StorefrontProvisioner::class))->toBeInstanceOf(ContainerStorefrontProvisioner::class);
});

it('creates and starts a storefront container and reports it ready', function (): void {
    fakeDockerEngine();

    $result = resolve(StorefrontProvisioner::class)->provision(storefrontRequest());

    expect($result->ready)->toBeTrue()
        ->and($result->reference)->toBe('vendra-storefront-acme-flowers')
        ->and($result->imageDigest)->toBe('sha256:abc123');

    assertDockerRequestSent(fn (Request $request): bool => $request->method === 'POST'
        && Str::contains($request->target(), '/containers/create')
        && Str::contains($request->target(), 'name=vendra-storefront-acme-flowers'));

    assertDockerRequestSent(fn (Request $request): bool => $request->method === 'POST'
        && Str::contains($request->target(), '/containers/vendra-storefront-acme-flowers/start'));
});

it('replaces the existing container so a redeploy is idempotent', function (): void {
    fakeDockerEngine();

    resolve(StorefrontProvisioner::class)->provision(storefrontRequest());

    assertDockerRequestSent(fn (Request $request): bool => $request->method === 'DELETE'
        && Str::contains($request->target(), '/containers/vendra-storefront-acme-flowers')
        && Str::contains($request->target(), 'force=true'));
});

it('routes the container with traefik labels the proxy already understands', function (): void {
    fakeDockerEngine();

    resolve(StorefrontProvisioner::class)->provision(storefrontRequest());

    assertDockerRequestSent(function (Request $request): bool {
        if (! Str::contains($request->target(), '/containers/create')) {
            return false;
        }

        $labels = Arr::get($request->body, 'Labels');

        return Arr::get($labels, 'traefik.enable') === 'true'
            && Arr::get($labels, 'traefik.docker.network') === 'traefik-public'
            && Arr::get($labels, 'traefik.http.routers.acme-flowers.rule') === 'Host(`acme.test`) || Host(`www.acme.test`)'
            && Arr::get($labels, 'traefik.http.routers.acme-flowers.entrypoints') === 'websecure'
            && Arr::get($labels, 'traefik.http.routers.acme-flowers.tls.certresolver') === 'letsencrypt'
            && Arr::get($labels, 'traefik.http.services.acme-flowers.loadbalancer.server.port') === '3000'
            && Arr::get($labels, 'traefik.http.services.acme-flowers.loadbalancer.healthcheck.path') === '/api/health'
            && Arr::get($labels, 'io.vendra.managed-by') === 'vendra'
            && Arr::get($labels, 'io.vendra.domain') === 'acme.test';
    });
});

it('passes the encoded configuration and estate settings as container environment', function (): void {
    fakeDockerEngine();

    $request = storefrontRequest();

    resolve(StorefrontProvisioner::class)->provision($request);

    assertDockerRequestSent(function (Request $sent) use ($request): bool {
        if (! Str::contains($sent->target(), '/containers/create')) {
            return false;
        }

        $data = $sent->body;

        return in_array('STOREFRONT_CONFIG_BASE64='.$request->encodedConfiguration(), Arr::get($data, 'Env'), true)
            && in_array('VENDRA_API_URL=https://api.vendra.test', Arr::get($data, 'Env'), true)
            && in_array('NODE_EXTRA_CA_CERTS=/certs/vendra-ca.crt', Arr::get($data, 'Env'), true)
            && in_array('NODE_ENV=production', Arr::get($data, 'Env'), true)
            && ['/var/lib/vendra/certificates:/certs:ro'] === Arr::get($data, 'HostConfig.Binds')
            && Arr::get($data, 'HostConfig.NetworkMode') === 'traefik-public'
            && ['no-new-privileges:true'] === Arr::get($data, 'HostConfig.SecurityOpt');
    });
});

it('records the deployment as requested when the container never turns healthy', function (): void {
    Config::set('vendra-store.storefront.health_timeout', 1);
    fakeDockerEngine(['Status' => 'running', 'Health' => ['Status' => 'starting']]);
    $deployment = StorefrontDeployment::factory()->create([
        'slug' => 'acme-flowers',
        'domain' => 'acme.test',
        'configuration' => storefrontConfiguration(),
    ]);

    runProvisionJob($deployment);

    expect($deployment->refresh()->status)->toBe(StorefrontDeploymentStatus::Requested)
        ->and($deployment->deployed_at)->toBeNull()
        ->and($deployment->container_name)->toBe('vendra-storefront-acme-flowers');
});

it('keeps a retrying deployment out of the failed state until the queue gives up', function (): void {
    fakeDockerEngine(['Status' => 'exited', 'ExitCode' => 1]);
    $deployment = StorefrontDeployment::factory()->create([
        'slug' => 'acme-flowers',
        'domain' => 'acme.test',
        'configuration' => storefrontConfiguration(),
    ]);

    $job = new ProvisionStorefrontJob($deployment->id);

    expect(fn () => app()->call($job->handle(...)))
        ->toThrow(RuntimeException::class, 'exited while starting');

    // A thrown attempt is not a failed deployment: it is still Processing and
    // the queue will come back to it.
    expect($deployment->refresh()->status)->toBe(StorefrontDeploymentStatus::Processing)
        ->and($deployment->error)->toBeNull();

    $job->failed(new RuntimeException('The storefront container exited during provisioning with code 1.'));

    expect($deployment->refresh()->status)->toBe(StorefrontDeploymentStatus::Failed)
        ->and($deployment->error)->toContain('exited during provisioning')
        ->and($deployment->failed_at)->not->toBeNull();
});

it('refuses to invent a network the estate owns', function (): void {
    fakeDockerEngine(networkExists: false);

    expect(fn () => resolve(StorefrontProvisioner::class)->provision(storefrontRequest()))
        ->toThrow(RuntimeException::class, 'does not exist');

    assertDockerRequestNotSent(fn (Request $request): bool => Str::contains($request->target(), '/containers/create'));
});

it('names the daemon it asked when a network is missing', function (): void {
    fakeDockerEngine(networkExists: false, serverHeader: 'Docker/29.7.2 (linux)');

    expect(fn () => resolve(StorefrontProvisioner::class)->provision(storefrontRequest()))
        ->toThrow(RuntimeException::class, 'does not exist on http://provisioner:8080 (Docker/29.7.2 (linux))');
});

/*
 | The incident this reports on: an endpoint that had been moved to the other
 | daemon still pinged, so the only symptom was a network that was plainly there
 | when the operator looked for it — on the runtime nobody was talking to.
 */
it('blames the daemon rather than the network when the endpoint is serving the other runtime', function (): void {
    fakeDockerEngine(networkExists: false, serverHeader: 'Libpod/5.8.6 (linux)');

    expect(fn () => resolve(StorefrontProvisioner::class)->provision(storefrontRequest()))
        ->toThrow(RuntimeException::class, 'serving podman while CONTAINER_DRIVER is set to docker');
});

it('does not blame the daemon when the engine is the configured one', function (): void {
    fakeDockerEngine(networkExists: false, serverHeader: 'Docker/29.7.2 (linux)');

    expect(fn () => resolve(StorefrontProvisioner::class)->provision(storefrontRequest()))
        ->toThrow(fn (RuntimeException $exception) => expect($exception->getMessage())->not->toContain('CONTAINER_DRIVER'));
});

it('rejects a configuration the storefront image would refuse to boot on', function (): void {
    fakeDockerEngine();
    $configuration = storefrontConfiguration();
    unset($configuration['businessType'], $configuration['contact']['email']);

    expect(fn () => resolve(StorefrontProvisioner::class)->provision(storefrontRequest([
        'configuration' => $configuration,
    ])))->toThrow(InvalidArgumentException::class, 'businessType, contact.email');

    assertNoDockerRequestsSent();
});

it('rejects a configuration whose identity drifted from the deployment', function (): void {
    fakeDockerEngine();

    expect(fn () => resolve(StorefrontProvisioner::class)->provision(storefrontRequest([
        'configuration' => storefrontConfiguration(['domain' => 'other.test']),
    ])))->toThrow(InvalidArgumentException::class, 'identity does not match');
});

it('resolves the digest from the pulled image when the reference is only a tag', function (): void {
    $created = false;

    bindFakeDockerEngine(function (Request $request, bool $stream) use (&$created) {
        $path = $request->path;

        if (Str::endsWith($path, '/containers/create')) {
            $created = true;

            return dockerResponse(['Id' => 'container-abc'], 201);
        }

        return match (true) {
            Str::endsWith($path, '/_ping') => dockerResponse('OK'),
            Str::contains($path, '/networks/') => dockerResponse(['Name' => 'traefik-public']),
            Str::endsWith($path, '/images/create') && $stream => dockerStreamResponse("{\"status\":\"Pulled\"}\n"),
            Str::contains($path, '/images/') => dockerResponse(['RepoDigests' => ['ghcr.io/misaf/vendra-storefront-florist@sha256:resolved']]),
            Str::endsWith($path, '/start') => dockerResponse('', 204),
            Str::contains($path, '/containers/') && Str::endsWith($path, '/json') => $created
                ? dockerResponse([
                    'Id' => 'container-abc',
                    'Name' => '/vendra-storefront-acme-flowers',
                    'State' => ['Status' => 'running', 'Health' => ['Status' => 'healthy']],
                ])
                : dockerResponse(['message' => 'no such container'], 404),
            $request->method === 'DELETE' => dockerResponse(['message' => 'no such container'], 404),
            default => $stream ? dockerStreamResponse('', 404) : dockerResponse('', 404),
        };
    });

    $result = resolve(StorefrontProvisioner::class)->provision(storefrontRequest([
        'image' => 'ghcr.io/misaf/vendra-storefront-florist:1.x',
    ]));

    expect($result->imageDigest)->toBe('sha256:resolved');
});

it('surfaces a pull failure reported inside the progress stream', function (): void {
    bindFakeDockerEngine(function (Request $request, bool $stream) {
        $path = $request->path;

        return match (true) {
            Str::endsWith($path, '/_ping') => dockerResponse('OK'),
            Str::contains($path, '/networks/') => dockerResponse(['Name' => 'traefik-public']),
            Str::endsWith($path, '/images/create') && $stream => dockerStreamResponse("{\"status\":\"Pulling\"}\n{\"error\":\"manifest unknown\"}\n"),
            default => $stream ? dockerStreamResponse('', 404) : dockerResponse('', 404),
        };
    });

    expect(fn () => resolve(StorefrontProvisioner::class)->provision(storefrontRequest()))
        ->toThrow(RuntimeException::class, 'manifest unknown');
});

describe('resource caps', function (): void {
    it('caps a storefront container from the fleet configuration', function (): void {
        Config::set('vendra-store.storefront.cpus', 0.5);
        Config::set('vendra-store.storefront.memory_megabytes', 512);
        Config::set('vendra-store.storefront.pids_limit', 512);
        fakeDockerEngine();

        resolve(StorefrontProvisioner::class)->provision(storefrontRequest());

        assertDockerRequestSent(function (Request $request): bool {
            if (! Str::contains($request->target(), '/containers/create')) {
                return false;
            }

            $hostConfig = Arr::get($request->body, 'HostConfig');

            return Arr::get($hostConfig, 'NanoCpus') === 500_000_000
                && Arr::get($hostConfig, 'Memory') === 536_870_912
                && Arr::get($hostConfig, 'PidsLimit') === 512;
        });
    });

    it('leaves a storefront uncapped when the fleet configures no limits', function (): void {
        Config::set('vendra-store.storefront.cpus', 0);
        Config::set('vendra-store.storefront.memory_megabytes', 0);
        Config::set('vendra-store.storefront.memory_reservation_megabytes', 0);
        Config::set('vendra-store.storefront.pids_limit', 0);
        fakeDockerEngine();

        resolve(StorefrontProvisioner::class)->provision(storefrontRequest());

        assertDockerRequestSent(function (Request $request): bool {
            if (! Str::contains($request->target(), '/containers/create')) {
                return false;
            }

            $hostConfig = Arr::get($request->body, 'HostConfig');

            return ! array_key_exists('NanoCpus', $hostConfig)
                && ! array_key_exists('Memory', $hostConfig)
                && ! array_key_exists('PidsLimit', $hostConfig);
        });
    });

    it('caps the PID count from the shipped default when nothing overrides it', function (): void {
        fakeDockerEngine();

        resolve(StorefrontProvisioner::class)->provision(storefrontRequest());

        assertDockerRequestSent(function (Request $request): bool {
            if (! Str::contains($request->target(), '/containers/create')) {
                return false;
            }

            return Arr::get($request->body, 'HostConfig.PidsLimit') === 512;
        });
    });

    it('honours a PID cap the operator overrides', function (): void {
        Config::set('vendra-store.storefront.pids_limit', 128);
        fakeDockerEngine();

        resolve(StorefrontProvisioner::class)->provision(storefrontRequest());

        assertDockerRequestSent(function (Request $request): bool {
            if (! Str::contains($request->target(), '/containers/create')) {
                return false;
            }

            return Arr::get($request->body, 'HostConfig.PidsLimit') === 128;
        });
    });
});

describe('podman compatibility', function (): void {
    it('applies the docker log driver and its rotation limits by default', function (): void {
        fakeDockerEngine();

        resolve(StorefrontProvisioner::class)->provision(storefrontRequest());

        assertDockerRequestSent(function (Request $request): bool {
            if (! Str::contains($request->target(), '/containers/create')) {
                return false;
            }

            return [
                'Type' => 'json-file',
                'Config' => ['max-size' => '10m', 'max-file' => '5'],
            ] === Arr::get($request->body, 'HostConfig.LogConfig');
        });
    });

    it('leaves logging to the runtime when no driver is named', function (): void {
        // Podman rejects json-file's options rather than ignoring them, so an
        // empty driver has to omit the block entirely, not send an empty one.
        Config::set('vendra-store.storefront.log_driver', '');
        fakeDockerEngine();

        resolve(StorefrontProvisioner::class)->provision(storefrontRequest());

        assertDockerRequestSent(function (Request $request): bool {
            if (! Str::contains($request->target(), '/containers/create')) {
                return false;
            }

            return ! array_key_exists('LogConfig', Arr::get($request->body, 'HostConfig'));
        });
    });

    it('sends a runtime-specific log driver without docker log options', function (): void {
        Config::set('vendra-store.storefront.log_driver', 'k8s-file');
        Config::set('vendra-store.storefront.log_options', []);
        fakeDockerEngine();

        resolve(StorefrontProvisioner::class)->provision(storefrontRequest());

        assertDockerRequestSent(function (Request $request): bool {
            if (! Str::contains($request->target(), '/containers/create')) {
                return false;
            }

            return ['Type' => 'k8s-file'] === Arr::get($request->body, 'HostConfig.LogConfig');
        });
    });

    it('surfaces an API version rejected by the configured driver', function (): void {
        bindFakeDockerEngine(fn(Request $request, bool $stream) => $stream
            ? dockerStreamResponse('', 500)
            : dockerResponse(['message' => 'client version 1.55 is too new'], 400));

        expect(fn () => resolve(StorefrontProvisioner::class)->provision(storefrontRequest()))
            ->toThrow(RuntimeException::class, 'client version 1.55 is too new');
    });

    it('reports an unreachable socket as unreachable', function (): void {
        bindFakeDockerEngine(fn (Request $request, bool $stream) => $stream
            ? dockerStreamResponse('', 500)
            : dockerResponse(['message' => 'The container runtime is not reachable.'], 500));

        expect(fn () => resolve(StorefrontProvisioner::class)->provision(storefrontRequest()))
            ->toThrow(RuntimeException::class, 'is not reachable');
    });

    it('deploys and warns when the runtime never runs the health check', function (): void {
        // Podman executes health checks through transient systemd timers; with
        // no systemd the state stays empty forever. The storefront still runs,
        // so it deploys — but the lost gate must not be silent.
        Log::spy();
        fakeDockerEngine(['Status' => 'running']);

        $result = resolve(StorefrontProvisioner::class)->provision(storefrontRequest());

        expect($result->ready)->toBeTrue();
        Log::shouldHaveReceived('warning')
            ->withArgs(fn (string $message): bool => Str::contains($message, 'no health state'))
            ->once();
    });
});
