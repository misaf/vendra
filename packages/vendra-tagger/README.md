# Vendra Tagger

Translated, sortable, and tenant-compatible tag management for Vendra applications, with a Filament administration resource built on `spatie/laravel-tags`.

## Features

- Uses Spatie's translated tag names and slugs
- Preserves custom slugs while generating missing slugs from names
- Uses a sortable `position` column in place of Spatie's `order_column`
- Derives optional tenant scoping and assignment from `misaf/vendra-support`
- Provides permission-backed Filament list, create, view, edit, delete, and reorder operations
- Supports configurable Filament panels and navigation groups
- Ships `ModelTagsInput`, `ModelTagsEntry`, and `ModelTagsColumn` so tag-consuming packages share one tags field, entry, and column

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- `misaf/vendra-support`
- `filament/spatie-laravel-tags-plugin`
- `spatie/laravel-tags`

## Installation

```bash
composer require misaf/vendra-tagger
php artisan vendor:publish --tag=tags-migrations
php artisan vendor:publish --tag=vendra-tagger-migrations
php artisan migrate
```

Configure Spatie Tags to use the Vendra model in `config/tags.php`:

```php
use Misaf\VendraTagger\Models\Tagger;

return [
    'tag_model' => Tagger::class,
];
```

The package service provider and Filament plugin are auto-discovered. Optionally publish the package configuration and translations:

```bash
php artisan vendor:publish --tag=vendra-tagger-config
php artisan vendor:publish --tag=vendra-tagger-translations
```

Seed the tag-management permissions when the Vendra permission module is installed:

```bash
php artisan vendra-tagger:seed
```

## Configuration

`config/vendra-tagger.php` exposes two integration options:

```php
return [
    'panels' => ['admin'],
    'navigation_group' => 'vendra-support::navigation.groups.Content',
];
```

`panels` accepts one panel ID or an array of IDs. `navigation_group` accepts a translation key or literal label and can also be overridden through `TaggerPlugin::navigationGroup()`.

Tenant awareness is not configured here. When a tenant provider binds an available `TenantResolver`, the package adds and scopes `tenant_id`; otherwise it operates without tenant ownership.

## Usage

Create a tag:

```php
use Misaf\VendraTagger\Models\Tagger;

$tag = Tagger::findOrCreate('Featured', type: 'content', locale: 'en');
```

Create a tag with an explicit translated slug:

```php
$tag = Tagger::query()->create([
    'name' => ['en' => 'Featured products'],
    'slug' => ['en' => 'hand-picked'],
    'type' => 'content',
]);
```

Load ordered tags by type:

```php
$tags = Tagger::withType('content')->get();
```

Attach tags to an Eloquent model using Spatie's trait:

```php
use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\HasTags;

final class Article extends Model
{
    use HasTags;
}

$article->attachTag('Featured', 'content');
```

### Vendra consumer integrations

Tagger binds the shared Support `TagResolver`. Product, User, Blog, Affiliate, Attribute, and FAQ detect that capability and enable their tag relations and Filament fields without depending on this package.

Create tags with the consumer's reserved type (`product`, `user`, `blog`, `affiliate`, `attribute`, or `faq`):

```php
$tag = Tagger::findOrCreate('Featured', type: 'product', locale: 'en');
```

Do not add Product imports or dependencies to Tagger. Shared relationship metadata belongs to `misaf/vendra-support`.

## Filament

By default, tag management is available on the `admin` panel in the shared Content navigation group. The resource supports translated name and slug editing, filtering, sorting, and drag-and-drop position updates. Names and slugs are validated within the current tenant and tag type; the type itself may be reused by any number of tags.

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-tagger
composer stan
```

From the Vendra monorepo:

```bash
vendor/bin/pest packages/vendra-tagger/tests
vendor/bin/phpstan analyse packages/vendra-tagger/src packages/vendra-tagger/database --memory-limit=2G
```

## License

MIT. See [LICENSE](LICENSE).
