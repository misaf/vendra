# Vendra Custom Page

Tenant-aware custom page management for Vendra applications.

## Features

- Custom page categories
- Custom pages with translatable content
- Filament resources on the `admin` panel

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- Livewire 4
- Pest 4
- Tailwind CSS 4
- `misaf/vendra-multimedia`
- `misaf/vendra-support`

## Installation

```bash
composer require misaf/vendra-custom-page
php artisan vendor:publish --tag=vendra-custom-page-migrations
php artisan migrate
```

Optional translations publish:

```bash
php artisan vendor:publish --tag=vendra-custom-page-translations
```

The service provider and Filament plugin are auto-registered.

## Usage

Create a page category:

```php
use Misaf\VendraCustomPage\Models\CustomPageCategory;

$category = CustomPageCategory::query()->create([
    'name' => ['en' => 'Company'],
    'description' => ['en' => 'Company pages'],
    'slug' => ['en' => 'company'],
    'position' => 1,
    'active' => true,
]);
```

Create a custom page:

```php
use Misaf\VendraCustomPage\Models\CustomPage;

CustomPage::query()->create([
    'custom_page_category_id' => $category->id,
    'name' => ['en' => 'About Us'],
    'description' => ['en' => 'About our company'],
    'slug' => ['en' => 'about-us'],
    'position' => 1,
    'active' => true,
]);
```

Load pages with their category:

```php
$pages = CustomPage::query()
    ->with('customPageCategory')
    ->where('active', true)
    ->get();
```

## Filament

Resources are available in the shared `Content` cluster on the `admin` panel:

- Custom Page Categories
- Custom Pages

Demo seeders use bundled JSON fixtures in production and when their declared factory classes are unavailable. Local monorepo development continues to use factories when they are autoloadable.

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-custom-page
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
