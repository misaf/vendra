<?php

declare(strict_types=1);

it('ships inquiry-specific Boost guidelines and a development skill', function (): void {
    $modulePath = dirname(__DIR__, 2);
    $guideline = file_get_contents($modulePath.'/resources/boost/guidelines/core.blade.php');
    $skill = file_get_contents($modulePath.'/resources/boost/skills/vendra-inquiry-development/SKILL.md');

    expect($guideline)->toBeString()
        ->toContain('## Vendra Inquiry', 'SubmitInquiryAction')
        ->and($skill)->toBeString()
        ->toContain('name: vendra-inquiry-development', '## Module Boundary');
});
