# Vendra Activity Log

Tenant-aware activity logging for Vendra applications.

## Features

- Tenant-scoped activity logs, plus platform activity (a null tenant id) for changes made outside any store, such as in the console; a change to a store's own row made outside the store is still logged under that store
- Store settings changes: saving a `spatie/laravel-settings` class that implements `ShouldLogActivity` logs the changed properties, old and new, under the `settings` log name with the settings group as the description
- Filament resource and widget on the `admin` panel
- Translation and migration publishing support
- Tenant-aware permission policy seeding

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- Livewire 4
- Pest 4
- Tailwind CSS 4
- `awcodes/filament-badgeable-column`
- `misaf/filament-jalali`
- `misaf/vendra-support`
- `spatie/laravel-activitylog`
- `spatie/laravel-settings`

## Installation

```bash
composer require misaf/vendra-activity-log
php artisan vendor:publish --tag=activitylog-migrations
php artisan vendor:publish --tag=vendra-activity-log-migrations
php artisan migrate
```

Set the activity model in `config/activitylog.php`:

```php
'activity_model' => \Misaf\VendraActivityLog\Models\ActivityLog::class,
```

Optional translations publish:

```bash
php artisan vendor:publish --tag=vendra-activity-log-translations
```

## Permissions

Seed activity log permissions for a tenant by ID or slug:

```bash
php artisan vendra-activity-log:seed {tenant} permissions
```

To run every activity log seeder:

```bash
php artisan vendra-activity-log:seed {tenant} all
```

## Usage

Use Spatie activity logging as usual:

```php
activity()
    ->causedBy(auth()->user())
    ->performedOn($model)
    ->withProperties(['key' => 'value'])
    ->log('Did something');
```

In Filament, logs are available on the `admin` panel.

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-activity-log
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
