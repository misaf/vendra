# Vendra FAQ

Tenant-aware FAQ management for Vendra applications.

## Features

- FAQ categories
- FAQs with translatable content
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

- `misaf/vendra-tagger` — enables assigning `faq`-typed tags through the shared Support resolver

## Installation

```bash
composer require misaf/vendra-faq
php artisan vendor:publish --tag=vendra-faq-migrations
php artisan migrate
```

Optional translations publish:

```bash
php artisan vendor:publish --tag=vendra-faq-translations
```

The service provider and Filament plugin are auto-registered.

## Usage

Create an FAQ category:

```php
use Misaf\VendraFaq\Models\FaqCategory;

$category = FaqCategory::query()->create([
    'name' => ['en' => 'General'],
    'description' => ['en' => 'General questions'],
    'slug' => ['en' => 'general'],
    'position' => 1,
    'active' => true,
]);
```

Create an FAQ:

```php
use Misaf\VendraFaq\Models\Faq;

Faq::query()->create([
    'faq_category_id' => $category->id,
    'name' => ['en' => 'How do I create an account?'],
    'description' => ['en' => 'Use the register page and verify your email.'],
    'slug' => ['en' => 'how-do-i-create-an-account'],
    'position' => 1,
    'active' => true,
]);
```

Load FAQs with their category:

```php
$faqs = Faq::query()
    ->with('faqCategory')
    ->where('active', true)
    ->get();
```

When Tagger is installed, FAQ forms and tables expose tags automatically. Create tags with the reserved `faq` type:

```php
use Misaf\VendraTagger\Models\Tagger;

Tagger::findOrCreate('Account', type: 'faq', locale: 'en');
```

FAQ imports neither Vendra Tagger nor Spatie Tags.

## Filament

Resources are available in the shared `Content` cluster on the `admin` panel:

- FAQ Categories
- FAQs

Demo seeders use bundled JSON fixtures in production and when their declared factory classes are unavailable. Local monorepo development continues to use factories when they are autoloadable.

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-faq
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
