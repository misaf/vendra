# Vendra Language

Tenant-aware language catalogs and database-backed translation lines for Vendra applications.

## Features

- A platform locale catalog backed by Symfony Intl and ICU
- Per-tenant enabled languages with exactly one default and sortable display order
- Platform languages and translation overrides for Console, separate from store rows
- Locale-specific database overrides powered by `spatie/laravel-translation-loader`
- A global Filament language switcher
- An optional bridge to `misaf/vendra-localization`

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- `misaf/vendra-support`
- `symfony/intl`

## Installation

```bash
composer require misaf/vendra-language
php artisan vendor:publish --tag=vendra-language-migrations
php artisan migrate
```

Optionally publish the configuration and translations:

```bash
php artisan vendor:publish --tag=vendra-language-config
php artisan vendor:publish --tag=vendra-language-translations
```

The service provider and Filament plugin are discovered automatically.

## Locale catalog

Every locale exposed by Symfony Intl may be installed for a tenant from the searchable Languages resource. The preferred locale subset for host integrations may still be configured in `config/vendra-language.php`:

```php
'locales' => ['en', 'de', 'fa', 'pt-BR'],
```

Values use canonical web/BCP-47 tags and must exist in the ICU locale catalog. This preferred subset does not limit which locales may be installed. Tenant language records store the locale, active state, default flag, and display position; localized names are derived from ICU.

## Usage

Enable a language for the current tenant:

```php
use Misaf\VendraLanguage\Models\Language;

$language = Language::query()->create([
    'locale' => 'en',
    'active' => true,
    'is_default' => true,
    'position' => 1,
]);
```

The first enabled language becomes the default automatically. Setting another language as default clears the previous default for the current tenant, and deleting the default promotes the first remaining language in display order.

Create a translation line and read it through Laravel's translator:

```php
use Misaf\VendraLanguage\Models\LanguageLine;

LanguageLine::query()->create([
    'group' => 'navigation',
    'key' => 'dashboard',
    'text' => [
        'en' => 'Dashboard',
        'de' => 'Übersicht',
        'fa' => 'داشبورد',
    ],
]);

__('navigation.dashboard');
```

To override a package translation, store the package translation namespace separately from the translation file group:

```php
LanguageLine::query()->create([
    'namespace' => 'vendra-product',
    'group' => 'attributes',
    'key' => 'name',
    'text' => ['en' => 'Product name'],
]);

__('vendra-product::attributes.name');
```

Leave `namespace` empty for application translations. In Laravel's `namespace::group.key` syntax, the package name is the namespace and the translation file name is the group.

Database values override only the locale for which a non-blank value is stored. Missing and blank values leave that locale's file translation intact, allowing Laravel's normal fallback behavior to run afterward.

Applications that replace `translation-loader.model` may implement `Misaf\VendraLanguage\Contracts\NamespacedLanguageLine` to receive package namespaces. Models without the contract continue to support application translations, while explicit custom loaders and translation managers remain authoritative.

Load the current tenant's enabled languages in display order:

```php
$languages = Language::query()->ordered()->get();
```

## Optional localization bridge

When `misaf/vendra-localization` is installed, this package supplies its supported locale catalog and appends the tenant-default resolver at the end of the resolver chain. Explicit user, route, query, and header preferences therefore retain priority.

## Filament

The configured Admin panel exposes Languages and Language Lines within the Localization cluster. Console uses separate resources for tenantless platform languages and translations. Language lines can override keys discovered in application and Vendra package translation files. Their form displays the active locales for the current store or platform and retains stored values for locales that are later disabled. Installed languages can be temporarily disabled from the Languages table without deleting them. The Languages table reports override coverage per locale, while the Language Lines table reports enabled-locale completion and identifies missing locales.

The global language switcher uses the current tenant's enabled languages in display order and falls back to `config('app.fallback_locale')` when none are enabled.

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-language
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
