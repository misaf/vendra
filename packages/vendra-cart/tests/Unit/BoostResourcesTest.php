<?php

declare(strict_types=1);

it('ships cart-specific Boost guidelines and a development skill', function (): void {
    $modulePath = dirname(__DIR__, 2);
    $guideline = file_get_contents($modulePath.'/resources/boost/guidelines/core.blade.php');
    $skill = file_get_contents($modulePath.'/resources/boost/skills/vendra-cart-development/SKILL.md');

    expect($guideline)->toBeString()
        ->toContain('## Vendra Cart', 'sellable')
        ->and($skill)->toBeString()
        ->toContain('name: vendra-cart-development', '## Module Boundary');
});
