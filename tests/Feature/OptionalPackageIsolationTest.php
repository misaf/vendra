<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Process;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraBlog\Models\BlogPost;
use Misaf\VendraFaq\Models\Faq;
use Misaf\VendraProduct\Models\Product;

it('boots a consumer with only its required Vendra packages', function (string $package, ?string $tagModel, array $additionalPackages = []): void {
    $filesystem = new Filesystem;
    $directory = sys_get_temp_dir().'/vendra-isolation-'.bin2hex(random_bytes(8));
    $installed = json_decode(file_get_contents(base_path('vendor/composer/installed.json')), true, flags: JSON_THROW_ON_ERROR);
    $manifests = array_column(Arr::get($installed, 'packages'), null, 'name');

    foreach (glob(base_path('packages/*/composer.json')) ?: [] as $manifestPath) {
        $manifest = json_decode(file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
        $manifests[Arr::get($manifest, 'name')] = $manifest;
    }

    $required = [];
    $pending = ['laravel/framework', 'misaf/'.$package, ...$additionalPackages];

    while ($pending !== []) {
        $dependency = array_pop($pending);

        if (isset($required[$dependency]) || ! isset($manifests[$dependency])) {
            continue;
        }

        $required[$dependency] = $manifests[$dependency];
        array_push($pending, ...array_keys($manifests[$dependency]['require'] ?? []));
    }

    try {
        foreach (['app', 'bootstrap/cache', 'config', 'storage/logs', 'storage/framework/views'] as $path) {
            $filesystem->ensureDirectoryExists($directory.'/'.$path);
        }

        $namespaces = [];
        $providers = [];

        foreach ($required as $name => $manifest) {
            array_push($providers, ...Arr::get($manifest, 'extra.laravel.providers', []));

            if (! str_starts_with($name, 'misaf/vendra-')) {
                continue;
            }

            $packageDirectory = substr($name, strlen('misaf/'));
            $target = $directory.'/packages/'.$packageDirectory;
            $source = base_path('packages/'.$packageDirectory);
            $filesystem->ensureDirectoryExists($target);
            $filesystem->copy($source.'/composer.json', $target.'/composer.json');

            foreach (['src', 'config', 'database', 'resources', 'routes'] as $path) {
                if ($filesystem->isDirectory($source.'/'.$path)) {
                    $filesystem->copyDirectory($source.'/'.$path, $target.'/'.$path);
                }
            }

            foreach (Arr::get($manifest, 'autoload.psr-4', []) as $namespace => $paths) {
                $namespaces[$namespace] = array_map(fn (string $path): string => $target.'/'.$path, (array) $paths);
            }
        }

        $filesystem->put($directory.'/composer.json', json_encode([
            'extra' => ['laravel' => ['dont-discover' => ['*']]],
        ], JSON_THROW_ON_ERROR));
        $filesystem->put($directory.'/config/auth.php', '<?php return '.var_export([
            'providers' => ['users' => ['driver' => 'eloquent', 'model' => User::class]],
        ], true).';');
        $filesystem->put($directory.'/isolation.json', json_encode([
            'namespaces' => $namespaces,
            'providers' => $providers,
            'packages' => array_keys($required),
            'tag_model' => $tagModel,
            'package' => $package,
        ], JSON_THROW_ON_ERROR));

        $result = Process::path($directory)->env([
            'APP_ENV' => 'testing',
            'APP_KEY' => 'base64:'.base64_encode(str_repeat('a', 32)),
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'CACHE_STORE' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'SESSION_DRIVER' => 'array',
        ])->timeout(30)->run([PHP_BINARY, '-r', <<<'CHILD'
            $repository = $argv[1];
            $isolation = json_decode(file_get_contents('isolation.json'), true, flags: JSON_THROW_ON_ERROR);
            require $repository.'/vendor/composer/ClassLoader.php';
            $loader = new Composer\Autoload\ClassLoader;
            foreach (require $repository.'/vendor/composer/autoload_psr4.php' as $namespace => $paths) {
                if (! str_starts_with($namespace, 'Misaf\\Vendra') && ! in_array($namespace, ['App\\', 'Tests\\'], true)) {
                    $loader->setPsr4($namespace, $paths);
                }
            }
            foreach ($isolation['namespaces'] as $namespace => $paths) {
                $loader->setPsr4($namespace, $paths);
            }
            $loader->addClassMap(array_filter(
                require $repository.'/vendor/composer/autoload_classmap.php',
                fn ($class) => ! str_starts_with($class, 'Misaf\\Vendra')
                    && ! str_starts_with($class, 'App\\') && ! str_starts_with($class, 'Tests\\'),
                ARRAY_FILTER_USE_KEY,
            ));
            $loader->register();
            foreach (require $repository.'/vendor/composer/autoload_files.php' as $file) {
                if (str_starts_with($file, $repository.'/vendor/') && ! str_contains($file, '/misaf/vendra-')) {
                    require_once $file;
                }
            }
            $installed = require $repository.'/vendor/composer/installed.php';
            $installed['versions'] = array_filter(
                $installed['versions'],
                fn ($name) => ! str_starts_with($name, 'misaf/vendra-') || in_array($name, $isolation['packages'], true),
                ARRAY_FILTER_USE_KEY,
            );
            Composer\InstalledVersions::reload($installed);

            $absent = [];
            foreach ([
                'vendra-order-api' => Misaf\VendraOrderApi\Providers\OrderApiServiceProvider::class,
                'vendra-attribute' => Misaf\VendraAttribute\Models\Attribute::class,
                'vendra-currency' => Misaf\VendraCurrency\Models\Currency::class,
                'vendra-tagger' => Misaf\VendraTagger\Models\Tagger::class,
                'vendra-localization' => Misaf\VendraLocalization\Contracts\LocaleResolver::class,
            ] as $package => $class) {
                if (in_array('misaf/'.$package, $isolation['packages'], true)) {
                    continue;
                }
                if (class_exists($class) || interface_exists($class) || Composer\InstalledVersions::isInstalled('misaf/'.$package)
                    || is_dir('packages/'.$package)) {
                    throw new RuntimeException('Optional package is still accessible: '.$package);
                }
                $absent[] = $package;
            }

            $app = Illuminate\Foundation\Application::configure(basePath: getcwd())
                ->withProviders($isolation['providers'], withBootstrapProviders: false)
                ->withExceptions()->create();
            $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
            Illuminate\Support\Facades\Exceptions::fake();
            config(['money.defaultCurrency' => 'EUR']);

            $checks = [
                'booted' => $app->isBooted(),
                'currency' => Misaf\VendraSupport\Capabilities\CurrencyIntegration::defaultCode() === 'EUR',
                'tags' => ! Misaf\VendraSupport\Capabilities\TagIntegration::isAvailable(),
            ];
            if ($isolation['tag_model'] !== null) {
                $tagModel = $isolation['tag_model'];
                try {
                    new $tagModel()->tags();
                    $checks['tag_relation'] = false;
                } catch (LogicException $exception) {
                    $checks['tag_relation'] = $exception->getMessage() === 'Install a tag provider to use tags.';
                }
            }
            if ($isolation['package'] === 'vendra-product') {
                $checks['product_currency'] = Misaf\VendraProduct\Models\ProductPrice::currencyOptions() === ['EUR' => 'EUR'];
                $checks['attributes'] = Misaf\VendraSupport\Capabilities\AttributeIntegration::options() === [];
                try {
                    new Misaf\VendraProduct\Models\Product()->attributeValues();
                    $checks['attribute_relation'] = false;
                } catch (LogicException) {
                    $checks['attribute_relation'] = true;
                }
            }
            if ($isolation['package'] === 'vendra-newsletter-api') {
                $checks['newsletter_processor'] = resolve(Misaf\VendraNewsletterApi\State\SubscribeNewsletterProcessor::class)
                    instanceof Misaf\VendraNewsletterApi\State\SubscribeNewsletterProcessor;
            }
            if ($isolation['package'] === 'vendra-language') {
                $checks['localization'] = config('vendra-localization') === null;
            }
            if ($isolation['package'] === 'vendra-transaction') {
                $checks['wallet_resolver'] = resolve(Misaf\VendraTransaction\Services\WalletResolver::class)
                    instanceof Misaf\VendraTransaction\Services\WalletResolver;
            }
            if ($isolation['package'] === 'vendra-order') {
                config(['app.currency' => 'EUR']);
                Illuminate\Database\Eloquent\Relations\Relation::morphMap([
                    'isolated_product' => Misaf\VendraProduct\Models\Product::class,
                ], merge: false);
                foreach (['vendra-order/create_orders_table', 'vendra-product/create_products_table'] as $migration) {
                    [$package, $name] = explode('/', $migration);
                    (require 'packages/'.$package.'/database/migrations/'.$name.'.php.stub')->up();
                }
                Illuminate\Support\Facades\Schema::withoutForeignKeyConstraints(function () {
                    Illuminate\Support\Facades\DB::table('products')->insert([
                        'id' => 1, 'product_category_id' => 1, 'name' => '{}', 'slug' => '{}',
                        'token' => 'stock-test', 'quantity' => 3, 'position' => 1,
                    ]);
                    Illuminate\Support\Facades\DB::table('orders')->insert([
                        'id' => 1, 'number' => 'isolated-order', 'status' => 'pending',
                        'currency_code' => 'EUR', 'stock_deducted' => true,
                    ]);
                    Illuminate\Support\Facades\DB::table('order_lines')->insert([
                        'order_id' => 1, 'sellable_type' => 'isolated_product', 'sellable_id' => 1,
                        'name' => '{}', 'currency_code' => 'EUR', 'quantity' => 2,
                    ]);
                });
                $order = Misaf\VendraOrder\Models\Order::query()->findOrFail(1);
                $order->cancel();
                $checks['stock_restored_without_api'] = Misaf\VendraProduct\Models\Product::query()->findOrFail(1)->quantity === 5;
                $checks['cancelled_without_api'] = $order->fresh()->status instanceof Misaf\VendraOrder\States\Cancelled;
                $checks['stock_flag_cleared'] = $order->fresh()->stock_deducted === false;
            }
            Illuminate\Support\Facades\Exceptions::assertNothingReported();
            echo json_encode(['absent' => $absent, 'checks' => $checks], JSON_THROW_ON_ERROR);
            CHILD, base_path()]);

        expect($result->exitCode())->toBe(0, $result->errorOutput().$result->output());
        $output = json_decode($result->output(), true, flags: JSON_THROW_ON_ERROR);
        expect(Arr::get($output, 'absent'))->toContain('vendra-currency', 'vendra-tagger', 'vendra-localization')
            ->and(Arr::get($output, 'checks'))->not->toBeEmpty();

        foreach (Arr::get($output, 'checks') as $check => $passed) {
            expect($passed)->toBeTrue($package.': '.$check);
        }
    } finally {
        $filesystem->deleteDirectory($directory);
    }
})->with([
    'order with product and without API' => ['vendra-order', null, ['misaf/vendra-product']],
    'product' => ['vendra-product', Product::class],
    'attribute' => ['vendra-attribute', Attribute::class],
    'faq' => ['vendra-faq', Faq::class],
    'affiliate' => ['vendra-affiliate', Affiliate::class],
    'blog' => ['vendra-blog', BlogPost::class],
    'user' => ['vendra-user', Misaf\VendraUser\Models\User::class],
    'transaction' => ['vendra-transaction', null],
    'language' => ['vendra-language', null],
    'newsletter API' => ['vendra-newsletter-api', null],
]);
