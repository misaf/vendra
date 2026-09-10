<?php

declare(strict_types=1);

it('ships delivery-specific Boost guidelines and a development skill', function (): void {
    $modulePath = dirname(__DIR__, 2);
    $guideline = file_get_contents($modulePath.'/resources/boost/guidelines/core.blade.php');
    $skill = file_get_contents($modulePath.'/resources/boost/skills/vendra-delivery-development/SKILL.md');

    expect($guideline)->toBeString()
        ->toContain('## Vendra Delivery', 'DeliveryZoneMatcher')
        ->and($skill)->toBeString()
        ->toContain('name: vendra-delivery-development', '## Module Boundary');
});
