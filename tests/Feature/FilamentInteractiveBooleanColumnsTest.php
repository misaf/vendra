<?php

declare(strict_types=1);

use Filament\Tables\Columns\IconColumn;

dataset('boolean active tables', [
    'attribute' => 'packages/vendra-attribute/src/Filament/Clusters/Resources/Attributes/Tables/AttributeTable.php',
    'blog post category' => 'packages/vendra-blog/src/Filament/Clusters/Resources/BlogPostCategories/Tables/BlogPostCategoryTable.php',
    'blog post' => 'packages/vendra-blog/src/Filament/Clusters/Resources/BlogPosts/Tables/BlogPostTable.php',
    'currency' => 'packages/vendra-currency/src/Filament/Clusters/Resources/Currencies/Tables/CurrencyTable.php',
    'custom page category' => 'packages/vendra-custom-page/src/Filament/Clusters/Resources/CustomPageCategories/Tables/CustomPageCategoryTable.php',
    'custom page' => 'packages/vendra-custom-page/src/Filament/Clusters/Resources/CustomPages/Tables/CustomPageTable.php',
    'FAQ category' => 'packages/vendra-faq/src/Filament/Clusters/Resources/FaqCategories/Tables/FaqCategoryTable.php',
    'FAQ' => 'packages/vendra-faq/src/Filament/Clusters/Resources/Faqs/Tables/FaqTable.php',
    'language' => 'packages/vendra-language/src/Filament/Clusters/Resources/Languages/Tables/LanguageTable.php',
    'product category' => 'packages/vendra-product/src/Filament/Clusters/Resources/ProductCategories/Tables/ProductCategoryTable.php',
    'transaction gateway' => 'packages/vendra-transaction/src/Filament/Clusters/Resources/TransactionGateways/Tables/TransactionGatewayTable.php',
    'user profile' => 'packages/vendra-user-profile/src/Filament/Clusters/Resources/Tables/UserProfileTable.php',
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

it('renders boolean active columns with the shared active toggle column', function (string $relativePath): void {
    $contents = file_get_contents(base_path($relativePath));

    expect($contents)->toBeString()
        ->toContain('IsActiveToggleColumn::make()')
        ->not->toContain("ToggleColumn::make('active')");
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
