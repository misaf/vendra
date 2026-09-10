<?php

declare(strict_types=1);

it('ships wishlist-specific Boost guidelines and a development skill', function (): void {
    $modulePath = dirname(__DIR__, 2);
    $guideline = file_get_contents($modulePath.'/resources/boost/guidelines/core.blade.php');
    $skill = file_get_contents($modulePath.'/resources/boost/skills/vendra-wishlist-development/SKILL.md');

    expect($guideline)->toBeString()
        ->toContain('## Vendra Wishlist', 'defaultFor')
        ->and($skill)->toBeString()
        ->toContain('name: vendra-wishlist-development', '## Module Boundary');
});
