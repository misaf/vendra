<?php

declare(strict_types=1);

use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use Misaf\VendraAffiliate\Database\Factories\AffiliateFactory;
use Misaf\VendraAffiliate\Models\AffiliateClick;
use Misaf\VendraCart\Database\Factories\CartFactory;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;

beforeEach(function (): void {
    config(['app.url' => 'http://localhost']);
    URL::forceRootUrl('http://localhost');

    makeCurrentTestTenant();
    $this->actingAs(createTestUser());
});

/**
 * @param  array<string, mixed>  $payload
 * @return array{response: TestResponse, body: array<string, mixed>}
 */
function rootMcpCall(array $payload, ?string $sessionId = null, string $url = 'http://localhost/mcp'): array
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
    $decoded = [];

    foreach (preg_split('/\r?\n/', $response->getContent()) ?: [] as $line) {
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

function rootMcpInitialize(string $url = 'http://localhost/mcp'): string
{
    $result = rootMcpCall([
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

    rootMcpCall(['jsonrpc' => '2.0', 'method' => 'notifications/initialized'], $sessionId, $url);

    return $sessionId;
}

it('requires authentication for the MCP transport', function (): void {
    auth()->logout();

    rootMcpCall([
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2025-06-18',
            'capabilities' => new stdClass,
            'clientInfo' => ['name' => 'pest', 'version' => '1.0'],
        ],
    ])['response']->assertUnauthorized();
});

it('advertises every API operation with object input schemas', function (): void {
    $sessionId = rootMcpInitialize();
    $body = rootMcpCall(['jsonrpc' => '2.0', 'id' => 2, 'method' => 'tools/list', 'params' => new stdClass], $sessionId)['body'];
    $tools = collect($body['result']['tools'] ?? []);

    expect($tools->pluck('name'))->toContain(
        'get_affiliate',
        'list_affiliates',
        'record_affiliate_visit',
        'get_attribute',
        'list_attributes',
        'get_attribute_value',
        'list_attribute_values',
        'get_blog_post',
        'list_blog_posts',
        'get_blog_post_category',
        'list_blog_post_categories',
        'get_cart',
        'list_carts',
        'get_custom_page',
        'list_custom_pages',
        'get_custom_page_category',
        'list_custom_page_categories',
        'get_faq',
        'list_faqs',
        'get_faq_category',
        'list_faq_categories',
        'get_multimedia',
        'list_multimedia',
        'get_product',
        'list_products',
        'get_product_category',
        'list_product_categories',
        'get_product_price',
        'list_product_prices',
    );

    $tools->each(fn (array $tool) => expect($tool['inputSchema']['type'] ?? null)->toBe('object'));
});

it('publishes readable API documentation as an MCP resource', function (): void {
    $sessionId = rootMcpInitialize();
    $listed = rootMcpCall(['jsonrpc' => '2.0', 'id' => 2, 'method' => 'resources/list', 'params' => new stdClass], $sessionId)['body'];

    expect(collect($listed['result']['resources'] ?? [])->pluck('uri'))
        ->toContain('resource://vendra/api-documentation');

    $read = rootMcpCall([
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'resources/read',
        'params' => ['uri' => 'resource://vendra/api-documentation'],
    ], $sessionId)['body'];

    expect(json_encode($read))->toContain('Vendra API', '/mcp', 'jsonld');
});

it('enforces resource policies for authenticated MCP tools', function (): void {
    $user = createTestUser();
    $owned = CartFactory::new()->forOwner($user)->create();
    $hidden = CartFactory::new()->forOwner(createTestUser())->create();
    $this->actingAs($user);
    $sessionId = rootMcpInitialize();

    $body = rootMcpCall([
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/call',
        'params' => ['name' => 'list_carts', 'arguments' => new stdClass],
    ], $sessionId)['body'];

    $cartIds = collect($body['result']['structuredContent']['member'] ?? [])->pluck('id');

    expect($body['result']['isError'] ?? false)->toBeFalse()
        ->and($cartIds)->toContain($owned->id)
        ->not->toContain($hidden->id);
});

it('validates and invokes the existing affiliate mutation', function (): void {
    $affiliate = AffiliateFactory::new()->active()->create();
    $sessionId = rootMcpInitialize();

    $body = rootMcpCall([
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/call',
        'params' => [
            'name' => 'record_affiliate_visit',
            'arguments' => [
                'code' => $affiliate->code,
                'landingUrl' => 'https://shop.test/products/1',
            ],
        ],
    ], $sessionId)['body'];

    expect($body['result']['isError'] ?? false)->toBeFalse()
        ->and(AffiliateClick::query()->whereBelongsTo($affiliate)->count())->toBe(1);

    $invalid = rootMcpCall([
        'jsonrpc' => '2.0',
        'id' => 4,
        'method' => 'tools/call',
        'params' => [
            'name' => 'record_affiliate_visit',
            'arguments' => ['code' => '', 'landingUrl' => 'not-a-url'],
        ],
    ], $sessionId)['body'];

    expect($invalid)->toHaveKey('error')
        ->and(AffiliateClick::query()->whereBelongsTo($affiliate)->count())->toBe(1);
});

it('resolves and isolates MCP calls by tenant domain', function (): void {
    forgetCurrentTestTenant();
    config()->set('vendra-tenant.central_host', 'vendra.test');

    $tenant = Store::factory()->active()->create(['slug' => 'flowers']);
    StoreDomain::factory()->for($tenant)->create([
        'name' => 'flowers.example.com',
        'active' => true,
    ]);

    $user = $tenant->execute(function () {
        $group = ProductCategoryFactory::new()->active()->create();
        ProductFactory::new()->forCategory($group)->create(['name' => ['en' => 'Visible Rose']]);

        return createTestUser();
    });

    $otherTenant = Store::factory()->active()->create(['slug' => 'other']);
    $otherTenant->execute(function (): void {
        $group = ProductCategoryFactory::new()->active()->create();
        ProductFactory::new()->forCategory($group)->create(['name' => ['en' => 'Hidden Tulip']]);
    });

    forgetCurrentTestTenant();
    $this->actingAs($user);
    $url = 'https://admin.flowers.example.com/mcp';
    $sessionId = rootMcpInitialize($url);

    $body = rootMcpCall([
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/call',
        'params' => ['name' => 'list_products', 'arguments' => new stdClass],
    ], $sessionId, $url)['body'];

    $serialized = json_encode($body['result'] ?? []);

    expect($serialized)->toContain('Visible Rose')
        ->not->toContain('Hidden Tulip')
        ->and(currentTestTenant())->toBeNull();
});
