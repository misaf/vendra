# Vendra

Vendra is a modular Laravel application for e-commerce and marketplace use cases.

## Requirements

- PHP 8.2+
- Composer
- Node.js and npm
- MySQL or another Laravel-supported database

## Local Development

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
composer dev
```

`composer dev` starts the web server, queue listener, logs, and Vite in watch mode.

## Docker Development

Docker deployments use the container entrypoint for application bootstrap.
No additional manual setup commands are required inside the container.

## Configuration

Settings cache can be enabled with:

```env
SETTINGS_CACHE_ENABLED=true
```

## Module Development

Modules are developed locally through Composer path repositories in `app-modules/*`.

Typical workflow:

1. Edit the module inside `app-modules/<module-name>`.
2. Ensure the package is required in root `composer.json`.
3. Run `composer update <vendor/package>` or `composer dump-autoload` when needed.
4. Run the relevant tests or static analysis.

For production builds, rely on installed Composer packages rather than local path repository workflows.

## Useful Commands

```bash
composer test
php artisan test --compact
vendor/bin/pint --dirty --format agent
vendor/bin/phpstan analyse
```

## Troubleshooting

- If package changes are not reflected, run `composer dump-autoload`.
- If provider discovery seems stale, run `php artisan package:discover`.
- If configuration values look outdated, run `php artisan config:clear`.
- If frontend changes do not appear, run `npm run dev` or `npm run build`.

## License

MIT. See [LICENSE](LICENSE).
