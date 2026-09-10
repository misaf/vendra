<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;

abstract class SeedCommand extends Command implements PromptsForMissingInput
{
    public function handle(): int
    {
        if (app()->isDownForMaintenance()) {
            $this->warn('Application is in maintenance mode. Command aborted.');

            return self::FAILURE;
        }

        $requestedSeeders = $this->requestedSeeders();

        if ($requestedSeeders === null) {
            $this->error('Invalid seeder selection. Seeder names must be strings.');

            return self::FAILURE;
        }

        $seederClasses = $this->resolveSeederClasses($requestedSeeders);

        if ($seederClasses === null) {
            $this->error(sprintf(
                'Invalid seeder selection. Available seeders: all, %s.',
                implode(', ', array_keys($this->seeders())),
            ));

            return self::FAILURE;
        }

        if (! $this->prepareForSeeding()) {
            return self::FAILURE;
        }

        foreach ($seederClasses as $seederClass) {
            $exitCode = $this->call('db:seed', [
                '--class' => $seederClass,
                '--force' => true,
            ]);

            if ($exitCode !== self::SUCCESS) {
                $this->error(sprintf(
                    'Seeder [%s] failed with exit code [%d].',
                    $seederClass,
                    $exitCode,
                ));

                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, callable>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'seeders' => fn () => $this->promptForSeeders(),
        ];
    }

    /**
     * @return array<string, class-string>
     */
    abstract protected function seeders(): array;

    protected function prepareForSeeding(): bool
    {
        return true;
    }

    /**
     * @return list<string>
     */
    private function promptForSeeders(): array
    {
        if (confirm(label: 'Run all seeders?', default: true)) {
            return ['all'];
        }

        return array_values(array_filter(multiselect(
            label: 'Which seeders should run?',
            options: array_keys($this->seeders()),
            required: true,
            hint: 'Choose one or more seeders.',
        ), is_string(...)));
    }

    /**
     * @param  list<string>  $seeders
     * @return list<class-string>|null
     */
    private function resolveSeederClasses(array $seeders): ?array
    {
        if ($seeders === []) {
            return null;
        }

        $available = $this->seeders();

        if (in_array('all', $seeders, true)) {
            return array_values($available);
        }

        $classes = [];

        foreach ($seeders as $seeder) {
            if (! array_key_exists($seeder, $available)) {
                return null;
            }

            $classes[] = $available[$seeder];
        }

        return $classes;
    }

    /**
     * @return list<string>|null
     */
    private function requestedSeeders(): ?array
    {
        $seeders = $this->argument('seeders');

        if (! is_array($seeders) || ! array_is_list($seeders)) {
            return null;
        }

        foreach ($seeders as $seeder) {
            if (! is_string($seeder)) {
                return null;
            }
        }

        return $seeders;
    }
}
