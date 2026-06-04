<?php

declare(strict_types=1);

use Misaf\VendraLanguage\Enums\LanguageLineGroupEnum;

test('language line groups expose labels for filament options', function (): void {
    expect(LanguageLineGroupEnum::Dashboard->getLabel())->toBe('Dashboard')
        ->and(LanguageLineGroupEnum::Modules->getLabel())->toBe('Modules');
});
