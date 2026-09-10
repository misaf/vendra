<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Misaf\VendraSupport\Tenancy\RequiresCurrentTenant;
use ReflectionClass;
use UnexpectedValueException;

abstract class DemoContentSeeder extends Seeder
{
    use RequiresCurrentTenant;

    protected const string FIXTURE_FILE = 'demo-content.json';

    final public function run(): void
    {
        if ($this->shouldUseFixtures()) {
            $this->seedFixtures($this->fixtureRecords());

            return;
        }

        $this->seedFactories();
    }

    abstract protected function seedFactories(): void;

    /**
     * @param  list<array<string, mixed>>  $records
     */
    abstract protected function seedFixtures(array $records): void;

    protected function shouldUseFixtures(): bool
    {
        return app()->isProduction();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fixtureRecords(): array
    {
        $decodedRecords = json_decode(
            File::get($this->fixturePath()),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        throw_if(! is_array($decodedRecords) || ! array_is_list($decodedRecords), UnexpectedValueException::class, 'Fixture file must contain a JSON array.');

        foreach ($decodedRecords as $decodedRecord) {
            throw_if(! is_array($decodedRecord) || array_is_list($decodedRecord), UnexpectedValueException::class, 'Each fixture record must be a JSON object.');
        }

        /** @var list<array<string, mixed>> $decodedRecords */
        return $decodedRecords;
    }

    private function fixturePath(): string
    {
        $fileName = new ReflectionClass(static::class)->getFileName();

        if ($fileName === false) {
            throw new UnexpectedValueException(sprintf('Unable to resolve fixture path for %s.', static::class));
        }

        return dirname($fileName).'/../fixtures/'.static::FIXTURE_FILE;
    }
}
