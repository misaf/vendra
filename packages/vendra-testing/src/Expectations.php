<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\Features\SupportTesting\Testable;
use Misaf\VendraTesting\TableSorting;
use Misaf\VendraTesting\TranslationParity;
use Pest\Expectation;
use PHPUnit\Framework\Assert;

if (function_exists('expect')) {
    /**
     * @param-closure-this Expectation<mixed> $this
     */
    expect()->extend('toHaveAtLeastTwoLocales', function (string $moduleName): Expectation {
        TranslationParity::assertModuleHasAtLeastTwoLocales(
            moduleName: $moduleName,
            languageDirectory: vendraTestingLanguageDirectory($this->value),
        );

        return $this;
    });

    /**
     * @param-closure-this Expectation<mixed> $this
     */
    expect()->extend('toHaveTranslationsInSync', function (string $moduleName): Expectation {
        TranslationParity::assertModuleTranslationsAreInSync(
            moduleName: $moduleName,
            languageDirectory: vendraTestingLanguageDirectory($this->value),
        );

        return $this;
    });

    /**
     * @param-closure-this Expectation<mixed> $this
     */
    expect()->extend('toHaveSortedTranslationKeys', function (string $moduleName): Expectation {
        TranslationParity::assertModuleTranslationKeysAreSorted(
            moduleName: $moduleName,
            languageDirectory: vendraTestingLanguageDirectory($this->value),
        );

        return $this;
    });

    /**
     * @param-closure-this Expectation<mixed> $this
     */
    expect()->extend('toSortByEverySortableColumn', function (array $recordsInAscendingOrder, bool $assertDescendingOrder = true): Expectation {
        TableSorting::assertSortsByEverySortableColumn(
            listPage: vendraTestingListPage($this->value),
            recordsInAscendingOrder: vendraTestingModels($recordsInAscendingOrder),
            assertDescendingOrder: $assertDescendingOrder,
        );

        return $this;
    });
}

function vendraTestingLanguageDirectory(mixed $languageDirectory): string
{
    if (! is_string($languageDirectory)) {
        Assert::fail('The expectation value must be a language directory path string.');
    }

    return $languageDirectory;
}

/**
 * @param  array<mixed>  $records
 * @return array<int, Model>
 */
function vendraTestingModels(array $records): array
{
    $models = [];

    foreach ($records as $record) {
        if (! $record instanceof Model) {
            Assert::fail('The expected records must be Eloquent models.');
        }

        $models[] = $record;
    }

    return $models;
}

/**
 * @return Testable<Component>
 */
function vendraTestingListPage(mixed $listPage): Testable
{
    if (! $listPage instanceof Testable) {
        Assert::fail('The expectation value must be a Livewire testable list page.');
    }

    return $listPage;
}
