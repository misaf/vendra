<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Misaf\VendraLanguage\Actions\SyncLanguageLinesAction;
use Misaf\VendraLanguage\Models\LanguageLine;
use Misaf\VendraLanguage\Support\TranslationCatalog;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();
});

it('imports missing package lines and locales without overwriting database translations', function (): void {
    $languageLine = LanguageLine::query()->create([
        'namespace' => 'vendra-sync-fixture',
        'group' => 'messages',
        'key' => 'welcome',
        'text' => [
            'en' => 'Customized welcome',
            'fa' => '',
        ],
    ]);

    $filesystem = new Filesystem;
    $loader = new FileLoader($filesystem, __DIR__.'/../Fixtures/translations');
    $loader->addNamespace('vendra-sync-fixture', __DIR__.'/../Fixtures/translations');
    $catalog = new TranslationCatalog($filesystem, new Translator($loader, 'en'));
    $syncLanguageLines = new SyncLanguageLinesAction($catalog);

    expect($syncLanguageLines->execute())->toBe([
        'created' => 1,
        'updated' => 1,
        'unchanged' => 0,
    ])->and($languageLine->refresh()->text)->toBe([
        'en' => 'Customized welcome',
        'fa' => '',
        'de' => 'Willkommen',
    ])->and(LanguageLine::query()->where([
        'namespace' => 'vendra-sync-fixture',
        'group' => 'messages',
        'key' => 'nested.label',
    ])->value('text'))->toBe([
        'de' => 'Bezeichnung',
        'en' => 'Label',
    ])->and($syncLanguageLines->execute())->toBe([
        'created' => 0,
        'updated' => 0,
        'unchanged' => 2,
    ]);
});
