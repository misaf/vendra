# Vendra

Vendra is a modular Laravel 12 application for e-commerce and marketplace use cases.

## Tech Stack

- PHP 8.4+
- Laravel 12
- Filament 4
- Livewire 3
- Pest 4
- Tailwind CSS 4

## Requirements

- PHP 8.2 or newer
- Composer
- Node.js and npm
- MySQL (or another configured Laravel-supported database)

## Quick Start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

For local development:

```bash
composer dev
```

This starts the web server, queue listener, logs, and Vite in watch mode.

## Repository Structure

- `app/` main application code
- `app-modules/` local path packages (modular features)
- `config/` framework and package configuration
- `database/` migrations, factories, seeders
- `resources/` views, frontend assets

## Modular Packages (`app-modules/*`)

This project uses Composer path repositories:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "app-modules/*",
      "options": {
        "symlink": true
      }
    }
  ]
}
```

Each package in `app-modules/*` can be developed locally and consumed by the app as a Composer dependency.

Typical package workflow:

1. Edit package code inside `app-modules/<package-name>`.
2. Ensure the package is required in root `composer.json`.
3. Run `composer update <vendor/package>` (or `composer dump-autoload`) when needed.
4. Run tests from the root app and/or package scope.

## SMS Gateway Packages in This Workspace

- `misaf/laravel-sms-gateway` (core manager/facade/contracts)
- `misaf/laravel-sms-gateway-ghasedak`
- `misaf/laravel-sms-gateway-sunway`
- `misaf/laravel-sms-gateway-kavenegar`
- `misaf/laravel-sms-gateway-smsir`

See `app-modules/laravel-sms-gateway/README.md` for full usage and extensibility details.

## Useful Commands

```bash
# Test suite
composer test

# Laravel tests directly
php artisan test --compact

# Code style
vendor/bin/pint --dirty --format agent

# Static analysis (if configured)
vendor/bin/phpstan analyse
```

## Troubleshooting

- If package changes are not reflected, run `composer dump-autoload`.
- If provider discovery seems stale, run `php artisan package:discover`.
- If configuration values look outdated, run `php artisan config:clear`.
- If frontend changes do not appear, run `npm run dev` or `npm run build`.
