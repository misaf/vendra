<?php

declare(strict_types=1);

use Filament\Tables\Columns\IconColumn;
use Illuminate\Support\Arr;

dataset('boolean active tables', [
    'attribute' => ['packages/vendra-attribute/src/Filament/Clusters/Resources/Attributes/Tables/AttributeTable.php', 'vendra-attribute::attributes.active'],
    'blog post category' => ['packages/vendra-blog/src/Filament/Clusters/Resources/BlogPostCategories/Tables/BlogPostCategoryTable.php', 'vendra-blog::attributes.active'],
    'blog post' => ['packages/vendra-blog/src/Filament/Clusters/Resources/BlogPosts/Tables/BlogPostTable.php', 'vendra-blog::attributes.active'],
    'currency' => ['packages/vendra-currency/src/Filament/Clusters/Resources/Currencies/Tables/CurrencyTable.php', 'vendra-currency::attributes.active'],
    'custom page category' => ['packages/vendra-custom-page/src/Filament/Clusters/Resources/CustomPageCategories/Tables/CustomPageCategoryTable.php', 'vendra-custom-page::attributes.active'],
    'custom page' => ['packages/vendra-custom-page/src/Filament/Clusters/Resources/CustomPages/Tables/CustomPageTable.php', 'vendra-custom-page::attributes.active'],
    'FAQ category' => ['packages/vendra-faq/src/Filament/Clusters/Resources/FaqCategories/Tables/FaqCategoryTable.php', 'vendra-faq::attributes.active'],
    'FAQ' => ['packages/vendra-faq/src/Filament/Clusters/Resources/Faqs/Tables/FaqTable.php', 'vendra-faq::attributes.active'],
    'language' => ['packages/vendra-language/src/Filament/Clusters/Resources/Languages/Tables/LanguageTable.php', 'vendra-language::attributes.active'],
    'product category' => ['packages/vendra-product/src/Filament/Clusters/Resources/ProductCategories/Tables/ProductCategoryTable.php', 'vendra-product::attributes.active'],
    'transaction gateway' => ['packages/vendra-transaction/src/Filament/Clusters/Resources/TransactionGateways/Tables/TransactionGatewayTable.php', 'vendra-transaction::attributes.active'],
    'user profile' => ['packages/vendra-user-profile/src/Filament/Clusters/Resources/Tables/UserProfileTable.php', 'vendra-user-profile::attributes.active'],
]);

dataset('localized boolean active labels', [
    'attribute' => ['vendra-attribute', 'active'],
    'blog' => ['vendra-blog', 'active'],
    'currency' => ['vendra-currency', 'active'],
    'custom page' => ['vendra-custom-page', 'active'],
    'FAQ' => ['vendra-faq', 'active'],
    'language' => ['vendra-language', 'active'],
    'newsletter subscriber' => ['vendra-newsletter', 'active'],
    'product' => ['vendra-product', 'active'],
    'transaction gateway' => ['vendra-transaction', 'active'],
    'user profile' => ['vendra-user-profile', 'active'],
]);

arch('filament tables use interactive toggles instead of icon columns')
    ->expect([
        'App\\Filament',
        'Misaf',
    ])
    ->not->toUse(IconColumn::class);

it('configures boolean active columns like the blog post table', function (string $relativePath, string $labelKey): void {
    $contents = file_get_contents(base_path($relativePath));

    expect($contents)->toBeString();

    $matched = preg_match(
        "/ToggleColumn::make\\('active'\\)(?<chain>.*?)(?=\\n\\s*[A-Z][A-Za-z]+Column::make\\(|\\n\\s*];)/s",
        $contents,
        $matches,
    );

    expect($matched)->toBe(1)
        ->and(Arr::get($matches, 'chain'))
        ->toContain("->label(__('{$labelKey}'))")
        ->toContain('->onIcon(Heroicon::Bolt)');
})->with('boolean active tables');

it('localizes boolean active labels', function (string $namespace, string $key): void {
    foreach (['en' => 'Active', 'de' => 'Aktiv', 'fa' => 'فعال'] as $locale => $label) {
        app()->setLocale($locale);

        expect(__("{$namespace}::attributes.{$key}"))->toBe($label);
    }
})->with('localized boolean active labels');

it('preserves labels for workflow statuses', function (): void {
    app()->setLocale('en');

    expect(__('vendra-newsletter::attributes.status'))->toBe('Status')
        ->and(__('vendra-transaction::attributes.status'))->toBe('Status');
});

it('does not expose a status component for authify logs without a status field', function (): void {
    foreach ([
        'packages/vendra-authify-log/src/Filament/Clusters/Resources/Tables/AuthifyLogTable.php',
        'packages/vendra-authify-log/src/Filament/Clusters/Resources/Schemas/AuthifyLogInfolist.php',
    ] as $relativePath) {
        $contents = file_get_contents(base_path($relativePath));

        expect($contents)
            ->toBeString()
            ->not->toContain("::make('status')");
    }
});
