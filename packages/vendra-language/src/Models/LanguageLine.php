<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Misaf\VendraLanguage\Contracts\NamespacedLanguageLine;
use Misaf\VendraLanguage\Database\Factories\LanguageLineFactory;
use Misaf\VendraLanguage\Observers\LanguageLineObserver;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Spatie\TranslationLoader\LanguageLine as SpatieLanguageLine;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string|null $namespace
 * @property string $group
 * @property string $key
 * @property array<string, string> $text
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Hidden(['tenant_id', 'namespace_guard'])]
#[ObservedBy([LanguageLineObserver::class])]
#[UseFactory(LanguageLineFactory::class)]
final class LanguageLine extends SpatieLanguageLine implements NamespacedLanguageLine, ShouldLogActivity
{
    use BelongsToTenant;

    /** @use HasFactory<LanguageLineFactory> */
    use HasFactory;

    /**
     * @return array<mixed>
     */
    public static function getTranslationsForGroup(string $locale, string $group, ?string $namespace = null): array
    {
        return Cache::rememberForever(
            self::getCacheKey($group, $locale, $namespace),
            fn (): array => self::query()
                ->where('namespace', $namespace)
                ->where('group', $group)
                ->get()
                ->reduce(function (array $lines, self $languageLine) use ($locale, $group): array {
                    $translation = $languageLine->text[$locale] ?? null;

                    if (! is_string($translation) || blank($translation)) {
                        return $lines;
                    }

                    if ($group === '*') {
                        $lines[$languageLine->key] = $translation;

                        return $lines;
                    }

                    Arr::set($lines, $languageLine->key, $translation);

                    return $lines;
                }, []),
        );
    }

    public static function getCacheKey(string $group, string $locale, ?string $namespace = null): string
    {
        $tenantId = TenantAwareness::currentId();
        $group = $namespace === null ? $group : "{$namespace}::{$group}";

        if ($tenantId !== null) {
            $group = "tenant-{$tenantId}.{$group}";
        }

        return parent::getCacheKey($group, $locale);
    }

    public function flushGroupCache(): void
    {
        foreach ($this->getTranslatedLocales() as $locale) {
            if (is_string($locale)) {
                Cache::forget(self::getCacheKey($this->group, $locale, $this->namespace));
            }
        }
    }

    protected function casts(): array
    {
        return [
            'namespace' => 'string',
            'tenant_id' => 'integer',
            ...parent::casts(),
        ];
    }

    /**
     * Public because LanguageLineObserver drives it; a private method was only
     * reachable while this ran in a same-class `booted()` closure.
     */
    public function flushOriginalTranslationCache(): void
    {
        $group = $this->getRawOriginal('group');
        $namespace = $this->getRawOriginal('namespace');
        $text = $this->getRawOriginal('text');

        if (! is_string($group)) {
            return;
        }

        if (is_string($text)) {
            $text = json_decode($text, true);
        }

        if (! is_array($text)) {
            return;
        }

        foreach (array_keys($text) as $locale) {
            if (is_string($locale)) {
                Cache::forget(self::getCacheKey(
                    $group,
                    $locale,
                    is_string($namespace) ? $namespace : null,
                ));
            }
        }
    }
}
