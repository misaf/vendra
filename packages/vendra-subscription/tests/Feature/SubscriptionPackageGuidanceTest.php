<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

it('describes the current generic polymorphic subscription architecture', function (): void {
    $packagePath = base_path('packages/vendra-subscription');
    $manifest = json_decode(File::get($packagePath.'/composer.json'), true, flags: JSON_THROW_ON_ERROR);
    $readme = File::get($packagePath.'/README.md');
    $guideline = File::get($packagePath.'/resources/boost/guidelines/core.blade.php');
    $skill = File::get($packagePath.'/resources/boost/skills/vendra-subscription-development/SKILL.md');

    expect(Arr::get($manifest, 'description'))->toBe('Generic plans and polymorphic subscriptions for Vendra applications')
        ->and($readme)
        ->toContain('Generic plans and polymorphic subscriptions')
        ->toContain('inverse `morphMany` relationship')
        ->not->toContain('vendra-subscription:provision')
        ->not->toContain('does not provide plan, billing, or recurring subscription models')
        ->and([$guideline, $skill])->each->toContain('subscriber_type` / `subscriber_id')
        ->toContain('stable morph alias')
        ->toContain('idempotency key')->not->toContain('NO Eloquent relation')->not->toContain('NO relation here');
});
