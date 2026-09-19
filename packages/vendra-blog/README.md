# Vendra Blog

Tenant-aware blog management for Vendra applications.

## Features

- Blog post categories
- Blog posts
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

Optional:

- `misaf/vendra-tagger` — enables assigning `blog`-typed tags to posts through `misaf/vendra-support`

## Installation

```bash
composer require misaf/vendra-blog
php artisan vendor:publish --tag=vendra-blog-migrations
php artisan migrate
```

Optional configuration and translations:

```bash
php artisan vendor:publish --tag=vendra-blog-config
php artisan vendor:publish --tag=vendra-blog-translations
```

The service provider and Filament plugin are auto-registered.

## Usage

Create a category:

```php
use Misaf\VendraBlog\Models\BlogPostCategory;

$category = BlogPostCategory::query()->create([
    'name' => ['en' => 'Announcements'],
    'active' => true,
]);
```

Create a post:

```php
use Misaf\VendraBlog\Models\BlogPost;

BlogPost::query()->create([
    'blog_post_category_id' => $category->id,
    'name' => ['en' => 'Welcome'],
    'active' => true,
]);
```

When Tagger is installed, blog post forms and tables expose tags automatically. Create tags with the reserved `blog` type:

```php
use Misaf\VendraTagger\Models\Tagger;

Tagger::findOrCreate('Announcement', type: 'blog', locale: 'en');
```

Blog imports neither Vendra Tagger nor Spatie Tags; the optional relationship is resolved through Support.

## Filament

Resources are available in the shared `Content` cluster on the `admin` panel:

- Blog Post Categories
- Blog Posts

Demo seeders use bundled JSON fixtures in production and when their declared factory classes are unavailable. Local monorepo development continues to use factories when they are autoloadable.

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-blog
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
