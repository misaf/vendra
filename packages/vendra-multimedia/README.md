# Vendra Multimedia

Tenant-aware media management for Vendra applications.

## Features

- Tenant-scoped Spatie Media Library model
- Tenant-grouped media storage paths (`{tenant}/{uuid}/`)
- Default WebP media conversions helper
- A per-store storage cap from the reseller's plan (`storage_megabytes_per_store`), checked on upload and on write
- `ModelImageUpload`, `ModelImageEntry`, and `ModelImageColumn` Filament components for model image collections
- Filament resource and optional widget on the `admin` panel
- Translation and migration publishing support

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- Livewire 4
- Pest 4
- `misaf/vendra-support`
- `spatie/laravel-medialibrary`

## Installation

```bash
composer require misaf/vendra-multimedia
php artisan vendor:publish --tag=medialibrary-migrations
php artisan vendor:publish --tag=vendra-multimedia-migrations
php artisan migrate
```

Set the media model and path generator in `config/media-library.php`:

```php
'media_model' => \Misaf\VendraMultimedia\Models\Multimedia::class,
'path_generator' => \Misaf\VendraMultimedia\Support\DefaultPathGenerator::class,
```

Optional translations publish:

```bash
php artisan vendor:publish --tag=vendra-multimedia-translations
```

## Usage

Use Spatie Media Library as usual:

```php
$model
    ->addMedia($pathToFile)
    ->toMediaCollection('default');
```

Models can reuse the default conversions:

```php
use Misaf\VendraMultimedia\Concerns\HasDefaultMediaConversions;

class Product extends Model implements HasMedia
{
    use HasDefaultMediaConversions;
    use InteractsWithMedia;
}
```

In Filament, media records are available on the `admin` panel.

## Storage layout

Media is stored under `{prefix}/{tenant}/{uuid}/`, with conversions and responsive images in `conversions/` and `responsive-images/` below it. The tenant comes from the media row rather than the current tenant, so paths resolve the same way in the console panel, the CLI, and queued jobs. Media without a tenant keeps the `{prefix}/{uuid}/` layout.

Move media stored before the tenant prefix into its tenant directory:

```bash
php artisan vendra-multimedia:relocate --dry-run
php artisan vendra-multimedia:relocate
```

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-multimedia
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
