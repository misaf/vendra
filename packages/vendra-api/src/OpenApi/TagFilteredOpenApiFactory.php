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
 * Restricts the generated OpenAPI document to the operations carrying one of the
 * tags requested through the `tags` query parameter, e.g.
 * `/api/docs.jsonopenapi?tags=Product`.
 */
final readonly class TagFilteredOpenApiFactory implements OpenApiFactoryInterface
{
    private const string QUERY_PARAMETER = 'tags';

    private const string SCHEMA_REF_PREFIX = '#/components/schemas/';

    /**
     * Every HTTP method exposed by a path item, mapped to its accessor pair.
     *
     * @var array<int, string>
     */
    private const array METHODS = ['Get', 'Put', 'Post', 'Delete', 'Options', 'Head', 'Patch', 'Trace'];

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
            $filtered = $this->filterPathItem($pathItem, $tags);

            if ($filtered instanceof PathItem) {
                $paths->addPath($path, $filtered);
            }
        }

        $openApi = $openApi
            ->withPaths($paths)
            ->withTags(array_values(array_filter(
                $openApi->getTags(),
                fn (Tag $tag): bool => $this->matches([$tag->getName()], $tags),
            )));

        return $this->pruneSchemas($openApi);
    }

    /**
     * Drops the component schemas that the remaining paths no longer reference.
     */
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

        $kept = new ArrayObject;

        foreach ($schemas as $name => $schema) {
            if (isset($reachable[$name])) {
                $kept[$name] = $schema;
            }
        }

        return $openApi->withComponents($components->withSchemas($kept));
    }

    /**
     * Recursively gathers every `#/components/schemas/*` reference held by the value.
     *
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
        $kept = false;

        foreach (self::METHODS as $method) {
            /** @var Operation|null $operation */
            $operation = $pathItem->{'get'.$method}();

            if (! $operation instanceof Operation) {
                continue;
            }

            if ($this->matches($operation->getTags() ?? [], $tags)) {
                $kept = true;

                continue;
            }

            $pathItem = $pathItem->{'with'.$method}(null);
        }

        return $kept ? $pathItem : null;
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

        $values = is_array($raw) ? $raw : explode(',', (string) $raw);

        return array_values(array_unique(array_filter(array_map(
            fn (mixed $value): string => mb_strtolower(mb_trim((string) $value)),
            $values,
        ))));
    }
}
