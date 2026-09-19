<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Misaf\DockerEngine\ApiVersion;
use Misaf\DockerEngine\DockerClient;
use Misaf\DockerEngine\Transport\Request;
use Misaf\DockerEngine\Transport\Response;
use Misaf\DockerEngine\Transport\StreamResponse;
use Misaf\LaravelDockerEngine\ContainerManager;
use Misaf\VendraStore\Models\StorefrontImage;
use Tests\Support\FakeDockerTransport;
use Tests\Support\StringDockerStream;
use Tests\TestCase;

pest()->extend(TestCase::class)->in(
    'Feature',
    '../packages/*/tests/Feature',
);

/**
 * Fake a Docker Engine where the storefront container already exists.
 *
 * Mutating calls are recorded on the returned object, which the fake keeps
 * updating after this function returns.
 *
 * @param  array<string, mixed>  $state
 * @return object{calls: list<string>, transport: FakeDockerTransport}
 */
function fakeExistingStorefront(
    array $state = ['Status' => 'running', 'Health' => ['Status' => 'healthy']],
    string $image = 'ghcr.io/misaf/vendra-storefront-florist@sha256:abc123',
    bool $present = true,
    string $logs = '',
): object {
    $recorder = new class
    {
        /** @var list<string> */
        public array $calls = [];

        public FakeDockerTransport $transport;
    };

    $transport = bindFakeDockerEngine(function (Request $request, bool $stream) use ($state, $image, &$present, $logs, $recorder): Response|StreamResponse {
        $path = $request->path;

        foreach (['/start', '/stop', '/restart', '/containers/create', '/images/create'] as $verb) {
            if (Str::endsWith($path, $verb)) {
                $recorder->calls[] = mb_ltrim($verb, '/');
            }
        }

        if ($request->method === 'DELETE' && Str::contains($path, '/containers/')) {
            $recorder->calls[] = 'remove';
        }

        if (Str::endsWith($path, '/logs')) {
            $recorder->calls[] = 'logs:'.rawurldecode((string) Str::of($path)->between('/containers/', '/logs'));
        }

        // A created container can be inspected from then on.
        if (Str::endsWith($path, '/containers/create')) {
            $present = true;
        }

        return match (true) {
            Str::endsWith($path, '/_ping') => dockerResponse('OK'),
            Str::contains($path, '/networks/') => dockerResponse(['Name' => 'traefik-public', 'Driver' => 'bridge']),
            Str::endsWith($path, '/images/create') && $stream => dockerStreamResponse("{\"status\":\"Pulled\"}\n"),
            Str::endsWith($path, '/logs') && $stream => dockerStreamResponse(dockerLogFrames($logs)),
            Str::endsWith($path, '/containers/create') => dockerResponse(['Id' => 'container-abc'], 201),
            Str::endsWith($path, '/start'), Str::endsWith($path, '/stop'),
            Str::endsWith($path, '/restart') => dockerResponse('', 204),
            Str::contains($path, '/containers/') && Str::endsWith($path, '/json') => $present
                ? dockerResponse([
                    'Id' => 'container-abc',
                    'Name' => '/vendra-storefront-acme-flowers',
                    'Config' => [
                        'Image' => $image,
                        'Labels' => [
                            'io.vendra.managed-by' => 'vendra',
                            'io.vendra.slug' => 'acme-flowers',
                        ],
                    ],
                    'State' => $state,
                ])
                : dockerResponse(['message' => 'no such container'], 404),
            $request->method === 'DELETE' => dockerResponse('', 204),
            default => $stream ? dockerStreamResponse('', 404) : dockerResponse('', 404),
        };
    });

    $recorder->transport = $transport;

    return $recorder;
}

/**
 * Fake a Docker Engine that accepts every call and reports a healthy container.
 *
 * The container is absent until `/containers/create` is called.
 *
 * @param  array<string, mixed>  $state
 */
function fakeDockerEngine(array $state = ['Status' => 'running', 'Health' => ['Status' => 'healthy']], bool $networkExists = true, ?string $serverHeader = null): FakeDockerTransport
{
    $created = false;

    return bindFakeDockerEngine(function (Request $request, bool $stream) use ($state, $networkExists, $serverHeader, &$created): Response|StreamResponse {
        $path = $request->path;
        $method = $request->method;

        if (Str::endsWith($path, '/containers/create')) {
            $created = true;

            return dockerResponse(['Id' => 'container-abc'], 201);
        }

        return match (true) {
            Str::endsWith($path, '/_ping') => dockerResponse('OK', headers: $serverHeader === null ? [] : ['Server' => [$serverHeader]]),
            Str::contains($path, '/networks/') => dockerResponse(
                $networkExists ? ['Name' => 'traefik-public', 'Driver' => 'bridge'] : ['message' => 'network not found'],
                $networkExists ? 200 : 404,
            ),
            Str::endsWith($path, '/images/create') && $stream => dockerStreamResponse("{\"status\":\"Pulled\"}\n"),
            Str::endsWith($path, '/start'),
            Str::endsWith($path, '/stop'),
            Str::endsWith($path, '/restart') => dockerResponse('', 204),
            Str::contains($path, '/containers/') && Str::endsWith($path, '/json') => $created
                ? dockerResponse([
                    'Id' => 'container-abc',
                    'Name' => '/vendra-storefront-acme-flowers',
                    'Config' => [
                        'Image' => 'ghcr.io/misaf/vendra-storefront-florist@sha256:abc123',
                        'Labels' => [
                            'io.vendra.managed-by' => 'vendra',
                            'io.vendra.slug' => 'acme-flowers',
                        ],
                    ],
                    'State' => $state,
                ])
                : dockerResponse(['message' => 'no such container'], 404),
            $method === 'DELETE' => dockerResponse(['message' => 'no such container'], 404),
            default => $stream ? dockerStreamResponse('', 404) : dockerResponse('', 404),
        };
    });
}

/** @param Closure(Request, bool): (Response|StreamResponse) $handler */
function bindFakeDockerEngine(Closure $handler): FakeDockerTransport
{
    $transport = new FakeDockerTransport($handler);
    $manager = resolve(ContainerManager::class);

    $manager->forgetDrivers();
    $manager->extend('docker', static fn (): DockerClient => new DockerClient($transport, ApiVersion::V1_55));
    currentFakeDockerEngine($transport);

    return $transport;
}

function currentFakeDockerEngine(?FakeDockerTransport $transport = null): FakeDockerTransport
{
    static $current;

    if ($transport instanceof FakeDockerTransport) {
        $current = $transport;
    }

    return $current ?? throw new LogicException('No fake Docker Engine is currently bound.');
}

/** @param Closure(Request): bool $callback */
function assertDockerRequestSent(Closure $callback): void
{
    expect(collect(currentFakeDockerEngine()->requests)->contains($callback))->toBeTrue();
}

/** @param Closure(Request): bool $callback */
function assertDockerRequestNotSent(Closure $callback): void
{
    expect(collect(currentFakeDockerEngine()->requests)->contains($callback))->toBeFalse();
}

function assertNoDockerRequestsSent(): void
{
    expect(currentFakeDockerEngine()->requests)->toBeEmpty();
}

/**
 * @param  array<array-key, mixed>|string  $body
 * @param  array<string, list<string>>  $headers
 */
function dockerResponse(array|string $body, int $status = 200, array $headers = []): Response
{
    return new Response(
        $status,
        $headers,
        is_array($body) ? json_encode($body, JSON_THROW_ON_ERROR) : $body,
    );
}

/** @param array<string, list<string>> $headers */
function dockerStreamResponse(string $body, int $status = 200, array $headers = []): StreamResponse
{
    return new StreamResponse($status, $headers, new StringDockerStream($body));
}

function dockerLogFrames(string $output): string
{
    return $output === '' ? '' : chr(1)."\0\0\0".pack('N', mb_strlen($output, '8bit')).$output;
}

/**
 * @return array<string, mixed>
 */
function storefrontRequestData(string $slug = 'acme-flowers'): array
{
    return [
        'storefront_image_id' => StorefrontImage::factory()->active()->create()->id,
        'storefront_slug' => $slug,
        'storefront_name_en' => 'Acme Flowers',
        'storefront_name_fa' => 'گل‌فروشی اکمی',
        'storefront_business_type' => 'Florist',
        'storefront_price_currency' => 'irr',
        'storefront_og_image' => '/images/og.webp',
        'storefront_locality' => 'Tehran',
        'storefront_country' => 'ir',
        'storefront_mobile_phone' => '09120000000',
        'storefront_office_phone' => '02100000000',
        'storefront_contact_email' => 'contact@acme.test',
        'storefront_hours_open' => '08:00',
        'storefront_hours_close' => '21:00',
        'storefront_map_query' => '35.7,51.4',
        'storefront_whatsapp_phone' => '+989120000000',
        'storefront_telegram_username' => 'acmeflowers',
        'storefront_instagram_username' => 'acmeflowers',
    ];
}
