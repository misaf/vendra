# Vendra Multimedia

Tenant-aware media management for Vendra applications.

## Features

- Tenant-scoped Spatie Media Library model
- UUID-based media storage paths
- Default WebP media conversions helper
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

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-multimedia
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
