# Vendra Permission

Role and permission management for Vendra applications.

## Features

- Role and permission resources in the shared `Customers` cluster
- Role and permission CRUD resources
- Manage role-permission relations from role pages
- Tenant-scoped Pennant feature flags for module/resource access
- Feature toggle Artisan command per tenant
- Policy classes and enums for role/permission actions
- Translation files for `en`, `de`, and `fa`
- Configurable `Gate::after()` admin bypass role

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- Livewire 4
- Pest 4
- Tailwind CSS 4
- `laravel/pennant`
- `misaf/vendra-support`
- `misaf/vendra-user`
- `awcodes/filament-badgeable-column`
- `misaf/filament-jalali`
- `spatie/laravel-permission`

## Installation

```bash
composer require misaf/vendra-permission
```

Publish Spatie Permission's configuration if it is not already present, then
publish Vendra's tenant-aware permission migration:

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag=permission-config
php artisan vendor:publish --tag=vendra-permission-migrations
php artisan migrate
```

Optional: publish module translations:

```bash
php artisan vendor:publish --tag=vendra-permission-translations
```

Optional: publish module config:

```bash
php artisan vendor:publish --tag=vendra-permission-config
```

If you use Pennant with the database driver, ensure Pennant storage is migrated in your application.

## Configuration

If you want Spatie to use this module's models everywhere in your app, set `config/permission.php`:

```php
'models' => [
    'permission' => Misaf\VendraPermission\Models\Permission::class,
    'role' => Misaf\VendraPermission\Models\Role::class,
],
```

The admin bypass role is configurable in `config/vendra-permission.php`:

```php
'admin_role' => env('VENDRA_PERMISSION_ADMIN_ROLE', RoleEnum::Admin->value),
```

The config key is `admin_role` (short and local to this file).
The env var is `VENDRA_PERMISSION_ADMIN_ROLE` (prefixed to avoid global collisions).
It defaults to `RoleEnum::Admin` — the top-level role granted to a tenant administrator; admins may create additional scoped roles themselves.
This value is used for both the `Gate::after()` admin bypass and excluding that role from the roles table query.

Pennant feature behavior is configured in `config/vendra-permission.php`:

```php
'features' => [
    'enabled' => env('VENDRA_PERMISSION_FEATURES_ENABLED', false),
    'defaults' => [
        'vendra-permission.module-enabled' => false,
        'vendra-permission.role-management' => false,
        'vendra-permission.permission-management' => false,
        'vendra-permission.bulk-role-assignment' => false,
    ],
],
```

## Filament

Resources are registered on the `admin` panel through `PermissionPlugin`:

- Roles
- Permissions

Both resources live in the shared `Customers` cluster.

Access is feature-gated against the current scope returned by the shared
`TenantResolver`:

- `vendra-permission.module-enabled` controls both resources
- `vendra-permission.role-management` controls role resource access
- `vendra-permission.permission-management` controls permission resource access
- `vendra-permission.bulk-role-assignment` controls attach/detach role bulk actions

## Pennant Features

Feature resolution must be enabled. Tenant-aware applications reject
non-tenant scopes; unresolved feature values use `features.defaults`.

Feature map:

| Enum case | Feature key | Short key | Effect |
| --- | --- | --- | --- |
| `ModuleEnabled` | `vendra-permission.module-enabled` | `module-enabled` | Enables or hides both permission resources |
| `RoleManagement` | `vendra-permission.role-management` | `role-management` | Enables or hides the roles resource |
| `PermissionManagement` | `vendra-permission.permission-management` | `permission-management` | Enables or hides the permissions resource |
| `BulkRoleAssignment` | `vendra-permission.bulk-role-assignment` | `bulk-role-assignment` | Enables or hides attach/detach role bulk actions |

## Artisan Commands

Toggle tenant features with:

```bash
php artisan vendra-permission:feature {activate|deactivate} {feature|all} {tenant}
```

`feature` accepts:

- enum case name, e.g. `RoleManagement`
- full key, e.g. `vendra-permission.role-management`
- short key, e.g. `role-management`
- `all`

`tenant` accepts:

- tenant `id`
- tenant `slug`

The command requires `vendra-permission.features.enabled` to be `true`.

Examples:

```bash
php artisan vendra-permission:feature activate module-enabled 1
php artisan vendra-permission:feature activate permission-management acme
php artisan vendra-permission:feature deactivate all acme
```

Seed permissions and demo data for a tenant with:

```bash
php artisan vendra-permission:seed {tenant}
```

## Usage

Use this package's tenant feature flags and console command to control access in each tenant:

- `vendra-permission.module-enabled`
- `vendra-permission.role-management`
- `vendra-permission.permission-management`
- `vendra-permission.bulk-role-assignment`

Role/permission CRUD and assignment semantics follow Spatie Permission.
See: https://spatie.be/docs/laravel-permission

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-permission
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
