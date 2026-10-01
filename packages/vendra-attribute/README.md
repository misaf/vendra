# Vendra Attribute

Reusable, tenant-agnostic attributes and polymorphic values for Vendra applications.

Own the concrete `Misaf\VendraAttribute\Support\EloquentAttributeResolver` adapter here and bind it to Support's `AttributeResolver` contract. Preserve configurable attribute/value models, column names, active filtering, ordering, and fallback behavior. Consumers depend on the shared contract rather than this adapter.

## Features

- Reusable attribute definitions with optional units
- Polymorphic values that can belong to products or any other Eloquent model
- Automatic tenant scoping through `misaf/vendra-support` when a tenant resolver is available
- Global operation when tenancy is not installed or enabled
- Sortable attributes and values
- Soft deletes
- Filament resource on configured panels
- English, German, and Persian translations

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- `misaf/vendra-support`

Optional:

- `misaf/vendra-tagger` — enables assigning `attribute`-typed tags through the shared Support resolver

## Installation

```bash
composer require misaf/vendra-attribute
php artisan vendor:publish --tag=vendra-attribute-migrations
php artisan migrate
```

Optional configuration and translations:

```bash
php artisan vendor:publish --tag=vendra-attribute-config
php artisan vendor:publish --tag=vendra-attribute-translations
```

The service provider and Filament plugin are auto-registered.

When `misaf/vendra-product` is also installed by the host application, its
attribute fields are enabled automatically through the shared Vendra Support
resolver. Neither package requires the other directly.

When `misaf/vendra-tagger` is installed, the Attribute form and table expose tags automatically. Create tags with the reserved `attribute` type:

```php
use Misaf\VendraTagger\Models\Tagger;

Tagger::findOrCreate('Product specification', type: 'attribute', locale: 'en');
```

Attribute imports neither Vendra Tagger nor Spatie Tags.

## Usage

Add attribute values to an Eloquent model with the provided concern:

```php
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraAttribute\Concerns\HasAttributeValues;

final class Product extends Model
{
    use HasAttributeValues;
}
```

Alternatively, a model may extend `Misaf\VendraAttribute\Models\AttributableModel`.

Create an attribute definition:

```php
use Misaf\VendraAttribute\Models\Attribute;

$weight = Attribute::query()->create([
    'name' => 'Weight',
    'description' => 'Shipping weight',
    'unit' => 'kg',
    'active' => true,
]);
```

Assign a value to any attributable model:

```php
$product->attributeValues()->create([
    'attribute_id' => $weight->id,
    'value' => '1.2',
]);
```

Load values with their definitions:

```php
$product->load('attributeValues.attribute');

foreach ($product->attributeValues as $attributeValue) {
    $name = $attributeValue->attribute->name;
    $value = $attributeValue->value;
    $unit = $attributeValue->attribute->unit;
}
```

## Tenant Awareness

The package never depends on a concrete tenant provider. Both `Attribute` and `AttributeValue` use the resolver-driven tenancy support from `misaf/vendra-support`.

- With the default null resolver, records are global and the migrations omit `tenant_id`.
- When a tenant provider binds an available resolver, migrations add `tenant_id`, and models are scoped and stamped automatically.

Do not assign `tenant_id` manually.

## Filament

Attribute definitions are managed in the shared `Catalog` cluster. By default,
the plugin is registered on the `admin` panel.

Configure panels, the navigation group, and available units in `config/vendra-attribute.php` after publishing the configuration.

## Seeding Permissions

```bash
php artisan vendra-attribute:seed
```

When tenancy is enabled, an optional tenant ID or slug may be supplied:

```bash
php artisan vendra-attribute:seed tenant-slug
```

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-attribute
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
