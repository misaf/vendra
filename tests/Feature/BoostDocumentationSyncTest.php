<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * @param  list<string>  $contractPhrases
 */
it('keeps package guidelines and skills aligned with documented source contracts', function (
    string $package,
    array $contractPhrases,
): void {
    $packagePath = base_path("packages/{$package}");
    $guideline = File::get($packagePath.'/resources/boost/guidelines/core.blade.php');
    $skillFiles = File::allFiles($packagePath.'/resources/boost/skills');

    expect($skillFiles)->toHaveCount(1);

    $skill = File::get(Arr::get($skillFiles, 0)->getPathname());

    foreach ($contractPhrases as $contractPhrase) {
        expect($guideline)->toContain($contractPhrase)
            ->and($skill)->toContain($contractPhrase);
    }
})->with([
    'attribute API resources' => [
        'vendra-attribute-api',
        ['AttributeResource', 'AttributeApiResolver', 'selectedAttributeValues'],
    ],
    'cart navigation' => [
        'vendra-cart',
        ['SalesCluster'],
    ],
    'subscription backlog observability' => [
        'vendra-subscription',
        ['ReportSubscriptionPaymentBacklogCommand'],
    ],
    'request and job context' => [
        'vendra-support',
        ['RequestJobContext'],
    ],
    'tenancy engine boundaries' => [
        'vendra-tenant',
        ['TenantContract', 'HostTenantFinder', '/caddy/domain-check'],
    ],
    'store accessibility and domains' => [
        'vendra-store',
        ['Store::accessible()', 'ReplaceStoreDomainAction', 'StoreResellerResolver'],
    ],
    'testing helpers' => [
        'vendra-testing',
        [
            'toSortByEverySortableColumn()',
            'makeCurrentTestTenantWithFeatures()',
            'setUpFilamentAdminTestContext()',
        ],
    ],
    'optional currency integration' => [
        'vendra-transaction',
        ['CurrencyIntegration'],
    ],
]);

it('only configures Vendra package skills that have a canonical definition', function (): void {
    $canonicalSkills = collect(File::glob(base_path('packages/*/resources/boost/skills/*/SKILL.md')))
        ->map(fn (string $path): string => basename(dirname($path)))
        ->sort()
        ->values()
        ->all();

    $boostConfig = json_decode(File::get(base_path('boost.json')), true, flags: JSON_THROW_ON_ERROR);
    $configuredSkills = collect(Arr::get($boostConfig, 'skills'))
        ->filter(fn (string $skill): bool => Str::startsWith($skill, 'vendra-'))
        ->sort()
        ->values()
        ->all();

    // boost.json curates which package skills are generated; the set may be a
    // subset, but every configured skill must resolve to a real SKILL.md so a
    // renamed or misspelled reference is still caught.
    expect(array_values(array_diff($configuredSkills, $canonicalSkills)))->toBeEmpty();
})->skip(
    fn (): bool => ! File::exists(base_path('boost.json')),
    'boost.json is a local-only Laravel Boost artifact and is not present in CI.',
);

it('keeps the transaction package free of stale direct currency guidance', function (): void {
    $packagePath = base_path('packages/vendra-transaction/resources/boost');
    $guideline = File::get($packagePath.'/guidelines/core.blade.php');
    $skill = File::get($packagePath.'/skills/vendra-transaction-development/SKILL.md');

    expect([$guideline, $skill])->each->not->toContain('depends on `misaf/vendra-currency`')->not->toContain('Currency coupling goes through `misaf/vendra-currency`');
});
