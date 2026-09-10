<?php

declare(strict_types=1);

namespace Misaf\VendraLocalization\Http\Middleware;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Misaf\VendraLocalization\Contracts\LocaleResolver;
use Misaf\VendraLocalization\Contracts\ProvidesVaryHeaders;
use Misaf\VendraLocalization\LocaleManager;
use Misaf\VendraLocalization\Resolvers\ChainLocaleResolver;
use Symfony\Component\HttpFoundation\Response;

final readonly class SetLocale
{
    public function __construct(
        private Container $container,
        private LocaleResolver $localeResolver,
        private LocaleManager $localeManager,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$sources): Response
    {
        $resolver = $this->resolver(array_values($sources));
        $locale = $this->resolveSupportedLocale($request, $resolver);

        $this->localeManager->apply($locale);

        $response = $next($request);
        $response->headers->set('Content-Language', $locale);
        $this->setVaryHeader($response, $resolver);

        return $response;
    }

    /**
     * Match the resolved locale against the supported locales, tolerating
     * region variants (e.g. fr-CA or fr_CA matches a supported fr).
     */
    private function supportedLocale(?string $locale): ?string
    {
        if ($locale === null) {
            return null;
        }

        $supportedLocales = $this->supportedLocalesByNormalizedValue();

        foreach ($this->localeCandidates($locale) as $candidate) {
            if (array_key_exists($candidate, $supportedLocales)) {
                return $supportedLocales[$candidate];
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $sources
     */
    private function resolver(array $sources): LocaleResolver
    {
        if ($sources === []) {
            return $this->localeResolver;
        }

        return ChainLocaleResolver::fromSources($this->container, $sources);
    }

    private function resolveSupportedLocale(Request $request, LocaleResolver $resolver): string
    {
        return $this->supportedLocale($resolver->resolve($request))
            ?? Config::string('app.fallback_locale');
    }

    private function setVaryHeader(Response $response, LocaleResolver $resolver): void
    {
        if (! $resolver instanceof ProvidesVaryHeaders) {
            return;
        }

        $headers = $resolver->varyHeaders();

        if ($headers === []) {
            return;
        }

        $response->headers->set('Vary', $headers, false);
    }

    /**
     * @return array<string, string>
     */
    private function supportedLocalesByNormalizedValue(): array
    {
        $supportedLocales = [];

        foreach (Config::array('vendra-localization.supported_locales', []) as $locale) {
            if (is_string($locale) && $locale !== '') {
                $supportedLocales[$this->normalizeLocale($locale)] = $locale;
            }
        }

        return $supportedLocales;
    }

    /**
     * @return list<string>
     */
    private function localeCandidates(string $locale): array
    {
        $normalizedLocale = $this->normalizeLocale($locale);

        return array_values(array_unique([
            $normalizedLocale,
            Str::before($normalizedLocale, '-'),
        ]));
    }

    private function normalizeLocale(string $locale): string
    {
        return Str::of($locale)
            ->replace('_', '-')
            ->lower()
            ->value();
    }
}
