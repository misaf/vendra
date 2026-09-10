<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Laravel\Pennant\Feature;
use Override;
use Spatie\Multitenancy\Tasks\SwitchRouteCacheTask;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        config()->set(
            'multitenancy.switch_tenant_tasks',
            array_values(array_filter(
                config()->array('multitenancy.switch_tenant_tasks'),
                static fn (mixed $task): bool => ! is_string($task) || $task !== SwitchRouteCacheTask::class,
            )),
        );

        Carbon::setLocale(app()->getLocale());
        Number::useLocale(app()->getLocale());
        Feature::flushCache();
    }
}
