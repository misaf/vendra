<?php

declare(strict_types=1);

namespace Misaf\VendraApi\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\McpResource;
use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Facades\Config;
use JsonException;
use Mcp\Schema\Content\TextResourceContents;
use Mcp\Schema\Result\ReadResourceResult;

#[ApiResource(
    operations: [],
    mcp: [
        'api_documentation' => new McpResource(
            uri: 'resource://vendra/api-documentation',
            name: 'vendra-api-documentation',
            description: 'Read the Vendra API identity, MCP endpoint, and enabled serialization formats.',
            mimeType: 'application/json',
            provider: [self::class, 'provide'],
        ),
    ],
)]
final readonly class ApiDocumentationResource
{
    /**
     * @param  array<int, string>  $formats
     */
    public function __construct(
        public string $title,
        public string $description,
        public string $version,
        public string $mcpEndpoint,
        public array $formats,
    ) {}

    /**
     * @param  array<string, mixed>  $uriVariables
     * @param  array<string, mixed>  $context
     *
     * @throws JsonException
     */
    public static function provide(Operation $operation, array $uriVariables = [], array $context = []): ReadResourceResult
    {
        $documentation = json_encode([
            'title' => Config::string('api-platform.title', 'Vendra API'),
            'description' => Config::string('api-platform.description', ''),
            'version' => Config::string('api-platform.version', '1.0.0'),
            'mcpEndpoint' => '/mcp',
            'formats' => array_keys(Config::array('api-platform.formats', [])),
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        return new ReadResourceResult([
            new TextResourceContents(
                uri: 'resource://vendra/api-documentation',
                mimeType: 'application/json',
                text: $documentation,
            ),
        ]);
    }
}
