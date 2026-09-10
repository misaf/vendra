<?php

declare(strict_types=1);

namespace Misaf\VendraLocalization\Resolvers;

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Misaf\VendraLocalization\Contracts\LocaleResolver;
use Misaf\VendraLocalization\Contracts\ProvidesVaryHeaders;

final readonly class ChainLocaleResolver implements LocaleResolver, ProvidesVaryHeaders
{
    public const array SOURCE_ALIASES = [
        'accept-language' => AcceptLanguageLocaleResolver::class,
        'query' => QueryLocaleResolver::class,
        'route' => RouteLocaleResolver::class,
        'user' => UserLocaleResolver::class,
    ];

    /** @var list<LocaleResolver> */
    private array $resolvers;

    public function __construct(LocaleResolver ...$resolvers)
    {
        $this->resolvers = array_values($resolvers);
    }

    /**
     * Build a chain from config values or middleware parameters, where each
     * source is a resolver class name or a SOURCE_ALIASES key.
     *
     * @param  array<mixed>  $sources
     */
    public static function fromSources(Container $container, array $sources): self
    {
        $resolvers = [];

        foreach ($sources as $source) {
            $resolvers[] = self::makeResolver($container, $source);
        }

        return new self(...$resolvers);
    }

    public function resolve(Request $request): ?string
    {
        foreach ($this->resolvers as $resolver) {
            $locale = $resolver->resolve($request);

            if ($locale !== null) {
                return $locale;
            }
        }

        return null;
    }

    public function varyHeaders(): array
    {
        $headers = [];

        foreach ($this->resolvers as $resolver) {
            if ($resolver instanceof ProvidesVaryHeaders) {
                $headers = [...$headers, ...$resolver->varyHeaders()];
            }
        }

        return array_values(array_unique($headers));
    }

    private static function makeResolver(Container $container, mixed $source): LocaleResolver
    {
        $class = self::sourceClass($source);
        $resolver = $class === null ? null : $container->make($class);

        if (! $resolver instanceof LocaleResolver) {
            throw new InvalidArgumentException(sprintf(
                'Locale resolver [%s] must implement [%s].',
                $class ?? get_debug_type($source),
                LocaleResolver::class,
            ));
        }

        return $resolver;
    }

    private static function sourceClass(mixed $source): ?string
    {
        if (! is_string($source)) {
            return null;
        }

        return self::SOURCE_ALIASES[$source] ?? $source;
    }
}
