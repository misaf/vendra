# Vendra Newsletter

Tenant-aware newsletter management for Vendra applications.

## Features

- Newsletters with a draft / scheduled / sent lifecycle
- Newsletter subscribers with subscribe and unsubscribe tracking
- Filament resources on the `admin` panel, grouped under `Marketing`

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- Livewire 4
- Pest 4
- Tailwind CSS 4
- `misaf/vendra-support`

## Installation

```bash
composer require misaf/vendra-newsletter
php artisan vendor:publish --tag=vendra-newsletter-migrations
php artisan migrate
```

Optional configuration and translations:

```bash
php artisan vendor:publish --tag=vendra-newsletter-config
php artisan vendor:publish --tag=vendra-newsletter-translations
```

The service provider and Filament plugin are auto-registered.

## Usage

Create a newsletter:

```php
use Misaf\VendraNewsletter\Enums\NewsletterStatusEnum;
use Misaf\VendraNewsletter\Models\Newsletter;

Newsletter::query()->create([
    'subject' => 'Monthly Update',
    'content' => '<p>Hello world.</p>',
    'status' => NewsletterStatusEnum::Draft,
]);
```

Add a subscriber (`subscribed_at` and the unsubscribe token are assigned automatically):

```php
use Misaf\VendraNewsletter\Models\NewsletterSubscriber;

NewsletterSubscriber::query()->create([
    'email' => 'reader@example.com',
    'name' => 'Reader',
]);
```

## Configuration

`config/vendra-newsletter.php` controls the Filament panels, navigation group, and the queue/batch settings used when sending a newsletter:

- `batch_chunk_size` — subscribers per dispatched batch job (`NEWSLETTER_BATCH_CHUNK_SIZE`)
- `queue.tries` — retry attempts for send jobs (`NEWSLETTER_QUEUE_TRIES`)
- `queue.timeout` — batch fan-out job timeout (`NEWSLETTER_QUEUE_TIMEOUT`)
- `queue.email_timeout` — per-recipient delivery job timeout (`NEWSLETTER_EMAIL_QUEUE_TIMEOUT`)

## Filament

Resources are available in the shared `Marketing` cluster on the `admin` panel:

- Newsletters
- Subscribers

Demo seeders use bundled JSON fixtures in production and when their declared factory classes are unavailable. Local monorepo development continues to use factories when they are autoloadable.

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-newsletter
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
