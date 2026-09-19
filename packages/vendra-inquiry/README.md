# Vendra Inquiry

Tenant-aware contact enquiries for Vendra applications: what someone wrote in
from the storefront, and whether a person has written back.

## Features

- Name, email, optional phone, optional occasion slug, and the message verbatim
- `new → answered → closed` status with a reopen path, badged in the admin inbox, as a Spatie model-states machine (`States\Open`, `Answered`, `Closed`) that refuses a move to the current status
- Validation that lives with the operation, so HTTP, console, and tests agree
- Sender locale stored so a reply can be written in the language they used
- Tenant-aware Filament inbox and permission seeding

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- `misaf/vendra-support`

## Installation

```bash
composer require misaf/vendra-inquiry
php artisan vendor:publish --tag=vendra-inquiry-migrations
php artisan migrate
```

Optionally publish the configuration and translations:

```bash
php artisan vendor:publish --tag=vendra-inquiry-config
php artisan vendor:publish --tag=vendra-inquiry-translations
```

## Recording an enquiry

```php
use Misaf\VendraInquiry\Actions\SubmitInquiryAction;

app(SubmitInquiryAction::class)->execute(
    name: 'Nasrin K.',
    email: 'nasrin@example.com',
    message: 'Two weddings in Mordad — do you still have dates?',
    occasion: 'wedding',
    source: 'contact-form',
    locale: 'fa',
);
```

This is a contact inbox, not a ticketing system: no threads, assignees, or
SLAs. A person answers by email and marks the enquiry answered.

## Testing

```bash
php artisan test --compact --testsuite=vendra-inquiry
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
