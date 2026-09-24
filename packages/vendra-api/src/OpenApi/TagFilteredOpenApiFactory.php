<?php

declare(strict_types=1);

namespace Misaf\VendraApi\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\Model\Tag;
use ApiPlatform\OpenApi\OpenApi;
use ArrayObject;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Request;
use Traversable;

/**
 * Limit the OpenAPI document to the `tags` query parameter, such as `?tags=Product`.
 */
final readonly class TagFilteredOpenApiFactory implements OpenApiFactoryInterface
{
    private const string QUERY_PARAMETER = 'tags';

    private const string SCHEMA_REF_PREFIX = '#/components/schemas/';

    public function __construct(private OpenApiFactoryInterface $decorated) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $tags = $this->requestedTags($context);

        if ($tags === []) {
            return $openApi;
        }

        $paths = new Paths;

        foreach ($openApi->getPaths()->getPaths() as $path => $pathItem) {
            if (! $pathItem instanceof PathItem) {
                continue;
            }

            $filtered = $this->filterPathItem($pathItem, $tags);

            if ($filtered instanceof PathItem) {
                $paths->addPath($path, $filtered);
            }
        }

        $openApi = $openApi
            ->withPaths($paths)
            ->withTags(array_values(array_filter(
                $openApi->getTags(),
                fn (mixed $tag): bool => $tag instanceof Tag && $this->matches([$tag->getName()], $tags),
            )));

        return $this->pruneSchemas($openApi);
    }

    private function pruneSchemas(OpenApi $openApi): OpenApi
    {
        $components = $openApi->getComponents();
        $schemas = $components->getSchemas();

        if ($schemas === null) {
            return $openApi;
        }

        $reachable = [];
        $pending = $this->collectSchemaReferences($openApi->getPaths()->getPaths());

        while ($pending !== []) {
            $name = array_pop($pending);

            if (isset($reachable[$name]) || ! isset($schemas[$name])) {
                continue;
            }

            $reachable[$name] = true;
            $pending = [...$pending, ...$this->collectSchemaReferences($schemas[$name])];
        }

        $kept = new ArrayObject(array_filter(
            $schemas->getArrayCopy(),
            static fn (int|string $name): bool => isset($reachable[$name]),
            ARRAY_FILTER_USE_KEY,
        ));

        return $openApi->withComponents($components->withSchemas($kept));
    }

    /**
     * @return array<int, string>
     */
    private function collectSchemaReferences(mixed $value): array
    {
        if (is_object($value)) {
            $value = $value instanceof Traversable ? iterator_to_array($value) : (array) $value;
        }

        if (! is_array($value)) {
            return is_string($value) && str_starts_with($value, self::SCHEMA_REF_PREFIX)
                ? [rawurldecode(mb_substr($value, mb_strlen(self::SCHEMA_REF_PREFIX)))]
                : [];
        }

        $references = [];

        foreach ($value as $item) {
            $references = [...$references, ...$this->collectSchemaReferences($item)];
        }

        return $references;
    }

    /**
     * @param  array<int, string>  $tags
     */
    private function filterPathItem(PathItem $pathItem, array $tags): ?PathItem
    {
        $keep = fn (?Operation $operation): ?Operation => $operation instanceof Operation
            && $this->matches(array_values(array_filter($operation->getTags() ?? [], is_string(...))), $tags)
                ? $operation
                : null;

        // Rebuild the item, since the options, head, and trace withers cannot clear an operation.
        $filtered = new PathItem(
            ref: $pathItem->getRef(),
            summary: $pathItem->getSummary(),
            description: $pathItem->getDescription(),
            get: $keep($pathItem->getGet()),
            put: $keep($pathItem->getPut()),
            post: $keep($pathItem->getPost()),
            delete: $keep($pathItem->getDelete()),
            options: $keep($pathItem->getOptions()),
            head: $keep($pathItem->getHead()),
            patch: $keep($pathItem->getPatch()),
            trace: $keep($pathItem->getTrace()),
            servers: $pathItem->getServers(),
            parameters: $pathItem->getParameters(),
            query: $pathItem->getQuery(),
            additionalOperations: $pathItem->getAdditionalOperations(),
        );

        $hasOperation = array_any(
            [$filtered->getGet(), $filtered->getPut(), $filtered->getPost(), $filtered->getDelete(), $filtered->getOptions(), $filtered->getHead(), $filtered->getPatch(), $filtered->getTrace()],
            static fn (?Operation $operation): bool => $operation instanceof Operation,
        );

        return $hasOperation ? $filtered : null;
    }

    /**
     * @param  array<int, string>  $operationTags
     * @param  array<int, string>  $requestedTags
     */
    private function matches(array $operationTags, array $requestedTags): bool
    {
        return array_any($operationTags, fn ($operationTag) => in_array(mb_strtolower($operationTag), $requestedTags, true));
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<int, string>
     */
    private function requestedTags(array $context): array
    {
        $request = Arr::get($context, 'request', null);
        $raw = $request instanceof Request ? $request->query->all()[self::QUERY_PARAMETER] ?? null : null;

        if ($raw === null || $raw === '' || $raw === []) {
            return [];
        }

        $values = match (true) {
            is_array($raw) => array_filter($raw, is_string(...)),
            is_string($raw) => explode(',', $raw),
            default => [],
        };

        return array_values(array_unique(array_filter(array_map(
            fn (string $value): string => mb_strtolower(mb_trim($value)),
            $values,
        ))));
    }
}
