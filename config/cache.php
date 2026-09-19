<?php

declare(strict_types=1);

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Error;
use ApiPlatform\Metadata\ErrorResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\McpResource;
use ApiPlatform\Metadata\McpTool;
use ApiPlatform\Metadata\McpToolCollection;
use ApiPlatform\Metadata\NotExposed;
use ApiPlatform\Metadata\Operations;
use ApiPlatform\Metadata\Parameters;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Property\PropertyNameCollection;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\OpenApi\Model\Parameter as OpenApiParameter;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\In;
use Misaf\VendraApi\State\EloquentResourceOptions;
use Symfony\Component\TypeInfo\Type\ArrayShapeType;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\GenericType;
use Symfony\Component\TypeInfo\Type\NullableType;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\TypeInfo\Type\UnionType;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\WebLink\Link as WebLinkLink;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache store that will be used by the
    | framework. This connection is utilized if another isn't explicitly
    | specified when running a cache operation inside the application.
    |
    */

    'default' => env('CACHE_STORE', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    | Supported drivers: "array", "database", "file", "memcached",
    |                    "redis", "dynamodb", "storage", "octane",
    |                    "session", "failover", "null"
    |
    */

    'stores' => [

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_CACHE_CONNECTION'),
            'table' => env('DB_CACHE_TABLE', 'cache'),
            'lock_connection' => env('DB_CACHE_LOCK_CONNECTION'),
            'lock_table' => env('DB_CACHE_LOCK_TABLE'),
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => storage_path('framework/cache/data'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
        ],

        'failover' => [
            'driver' => 'failover',
            'stores' => [
                'database',
                'array',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing the APC, database, memcached, Redis, and DynamoDB cache
    | stores, there might be other applications using the same cache. For
    | that reason, you may prefix every cache key to avoid collisions.
    |
    */

    'prefix' => env('CACHE_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-cache-'),

    /*
    |--------------------------------------------------------------------------
    | Serializable Classes
    |--------------------------------------------------------------------------
    |
    | This value determines the classes that can be unserialized from cache
    | storage. By default, no PHP classes will be unserialized from your
    | cache to prevent gadget chain attacks if your APP_KEY is leaked.
    |
    | API Platform caches its metadata, so every class in that object graph
    | must be listed here. When new resources add operation, parameter, or
    | property types, rerun tests/Feature/CacheSerializableClassesTest.php.
    |
    */

    'serializable_classes' => [
        stdClass::class,
        Collection::class,
        CarbonImmutable::class,

        // API Platform resource and property metadata.
        ApiProperty::class,
        ApiResource::class,
        Delete::class,
        Error::class,
        ErrorResource::class,
        Get::class,
        GetCollection::class,
        Link::class,
        NotExposed::class,
        Operations::class,
        Parameters::class,
        McpResource::class,
        McpTool::class,
        McpToolCollection::class,
        OpenApiOperation::class,
        OpenApiParameter::class,
        Post::class,
        PropertyNameCollection::class,
        QueryParameter::class,
        ResourceMetadataCollection::class,

        // Eloquent state options on each resource.
        EloquentResourceOptions::class,

        // The `Rule::in()` built for schema enums, which every sort parameter has.
        In::class,

        // Symfony type metadata inside ApiProperty and QueryParameter.
        ArrayShapeType::class,
        BuiltinType::class,
        CollectionType::class,
        GenericType::class,
        NullableType::class,
        ObjectType::class,
        TypeIdentifier::class,
        UnionType::class,
        WebLinkLink::class,
    ],
];
