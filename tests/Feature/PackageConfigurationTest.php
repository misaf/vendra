<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Config;

/**
 * @return list<string>
 */
function packageConfigurationFiles(): array
{
    $configurationFiles = glob(dirname(__DIR__, 2).'/packages/*/config/*.php');

    expect($configurationFiles)->not->toBeFalse();

    sort($configurationFiles);

    return $configurationFiles;
}

it('loads every package configuration file through Laravel', function (string $configurationFile): void {
    $configurationName = pathinfo($configurationFile, PATHINFO_FILENAME);

    expect(Config::has($configurationName))->toBeTrue();
})->with(fn (): array => packageConfigurationFiles());

it('uses package-aligned filenames and strict PHP configuration', function (string $configurationFile): void {
    $configurationName = pathinfo($configurationFile, PATHINFO_FILENAME);
    $packageName = basename(dirname($configurationFile, 2));
    $contents = file_get_contents($configurationFile);

    expect($configurationName)->toBe($packageName)
        ->and($contents)->not->toBeFalse()
        ->and($contents)->toContain('declare(strict_types=1);');
})->with(fn (): array => packageConfigurationFiles());

it('keeps package configuration cache safe and lowercase', function (string $configurationFile): void {
    $configuration = require $configurationFile;

    expect($configuration)->toBeArray();

    $assertCacheSafe = function (array $values) use (&$assertCacheSafe): void {
        foreach ($values as $key => $value) {
            if (is_string($key)) {
                expect($key)->toBe(mb_strtolower($key));
            }

            if (is_array($value)) {
                $assertCacheSafe($value);

                continue;
            }

            expect(is_scalar($value) || $value === null)->toBeTrue();
        }
    };

    $assertCacheSafe($configuration);
})->with(fn (): array => packageConfigurationFiles());

it('casts newsletter environment settings for strict config accessors', function (): void {
    $environment = Env::getRepository();
    $variables = [
        'NEWSLETTER_BATCH_CHUNK_SIZE' => '250',
        'NEWSLETTER_QUEUE_TRIES' => '5',
        'NEWSLETTER_QUEUE_TIMEOUT' => '600',
        'NEWSLETTER_EMAIL_QUEUE_TIMEOUT' => '45',
    ];

    foreach ($variables as $name => $value) {
        $environment->set($name, $value);
    }

    try {
        $configuration = require base_path('packages/vendra-newsletter/config/vendra-newsletter.php');

        expect(Arr::get($configuration, 'batch_chunk_size'))->toBeInt()->toBe(250)
            ->and(Arr::get($configuration, 'queue.tries'))->toBeInt()->toBe(5)
            ->and(Arr::get($configuration, 'queue.timeout'))->toBeInt()->toBe(600)
            ->and(Arr::get($configuration, 'queue.email_timeout'))->toBeInt()->toBe(45);
    } finally {
        foreach (array_keys($variables) as $name) {
            $environment->clear($name);
        }
    }
});
