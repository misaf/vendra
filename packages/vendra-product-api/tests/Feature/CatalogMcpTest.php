<?php

declare(strict_types=1);

use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;

beforeEach(function (): void {
    // The MCP transport's DNS-rebinding guard only permits localhost-family
    // hosts, so drive the endpoint through a localhost root URL.
    config(['app.url' => 'http://localhost']);
    URL::forceRootUrl('http://localhost');

    makeCurrentTestTenant();
    $this->actingAs(createTestUser());
});

/**
 * Perform a JSON-RPC call against the MCP endpoint, decoding the JSON or
 * Server-Sent-Events payload the streamable HTTP transport returns.
 *
 * @param  array<string, mixed>  $payload
 * @return array{response: TestResponse, body: array<string, mixed>}
 */
function mcpCall(array $payload, ?string $sessionId = null, string $url = 'http://localhost/mcp'): array
{
    $host = parse_url($url, PHP_URL_HOST);
    $headers = [
        'Accept' => 'application/json, text/event-stream',
        'Host' => is_string($host) ? $host : 'localhost',
    ];

    if ($sessionId !== null) {
        $headers['Mcp-Session-Id'] = $sessionId;
    }

    $response = test()->postJson($url, $payload, $headers);

    $content = $response->getContent();

    $decoded = [];
    foreach (preg_split('/\r?\n/', $content) ?: [] as $line) {
        $line = str_starts_with($line, 'data:') ? mb_trim(mb_substr($line, 5)) : mb_trim($line);

        if ($line === '') {
            continue;
        }

        $json = json_decode($line, true);
        if (is_array($json)) {
            $decoded = $json;
        }
    }

    return ['response' => $response, 'body' => $decoded];
}

/**
 * Run the initialize handshake and return the negotiated session id.
 */
function mcpInitialize(string $url = 'http://localhost/mcp'): string
{
    $result = mcpCall([
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2025-06-18',
            'capabilities' => new stdClass,
            'clientInfo' => ['name' => 'pest', 'version' => '1.0'],
        ],
    ], url: $url);

    $result['response']->assertOk();

    $sessionId = $result['response']->headers->get('Mcp-Session-Id');
    expect($sessionId)->not->toBeNull();

    mcpCall(['jsonrpc' => '2.0', 'method' => 'notifications/initialized'], $sessionId, $url);

    return $sessionId;
}

it('advertises the product API operations with object input schemas', function (): void {
    $sessionId = mcpInitialize();

    $body = mcpCall(['jsonrpc' => '2.0', 'id' => 2, 'method' => 'tools/list', 'params' => new stdClass], $sessionId)['body'];

    $tools = collect($body['result']['tools'] ?? []);
    $names = $tools->pluck('name');

    expect($names)->toContain(
        'get_product',
        'list_products',
        'get_product_category',
        'list_product_categories',
        'get_product_price',
        'list_product_prices',
    );

    // MCP rejects any tool whose input schema is not a JSON object.
    $tools->each(fn (array $tool) => expect($tool['inputSchema']['type'] ?? null)->toBe('object'));
});

it('returns active catalog products when the list_products tool is called', function (): void {
    $group = ProductCategoryFactory::new()->active()->create();
    $product = ProductFactory::new()->forCategory($group)->create();

    $sessionId = mcpInitialize();

    $body = mcpCall([
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/call',
        'params' => ['name' => 'list_products', 'arguments' => new stdClass],
    ], $sessionId)['body'];

    expect($body['result']['isError'] ?? false)->toBeFalse();

    $structured = $body['result']['structuredContent'] ?? $body['result'] ?? [];

    expect(json_encode($structured))->toContain((string) $product->id);
});

it('gets one API resource by identifier', function (): void {
    $group = ProductCategoryFactory::new()->active()->create();
    $product = ProductFactory::new()->forCategory($group)->create();
    $sessionId = mcpInitialize();

    $body = mcpCall([
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/call',
        'params' => ['name' => 'get_product', 'arguments' => ['id' => $product->id]],
    ], $sessionId)['body'];

    expect($body['result']['isError'] ?? false)->toBeFalse()
        ->and(json_encode($body['result'] ?? []))->toContain((string) $product->id);
});
