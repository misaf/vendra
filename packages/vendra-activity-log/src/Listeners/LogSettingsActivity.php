<?php

declare(strict_types=1);

namespace Misaf\VendraActivityLog\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Context;
use Misaf\VendraSupport\Context\RequestJobContext;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Spatie\LaravelSettings\Events\SavingSettings;

/**
 * Log the properties a save changes on a settings class that implements ShouldLogActivity.
 *
 * Settings rows are written through the query builder, so no Eloquent event
 * fires. The stored values are read from a fresh instance because a filled
 * settings object never loads its originals. A save with no current store
 * writes the platform row and is logged as platform activity.
 */
final class LogSettingsActivity
{
    public const string LOG_NAME = 'settings';

    public function handle(SavingSettings $event): void
    {
        $settings = $event->settings;

        if (! $settings instanceof ShouldLogActivity || ! Config::boolean('activitylog.enabled', true)) {
            return;
        }

        $old = (new ($settings::class)())->toArray();
        $new = $event->properties->all();

        $changed = array_keys(array_filter(
            $new,
            fn (mixed $value, string $name): bool => ! array_key_exists($name, $old) || $old[$name] !== $value,
            ARRAY_FILTER_USE_BOTH,
        ));

        if ($changed === []) {
            return;
        }

        activity(self::LOG_NAME)
            ->event('updated')
            ->withChanges([
                'attributes' => Arr::only($new, $changed),
                'old' => Arr::only($old, $changed),
            ])
            ->withProperties($this->properties())
            ->log($settings::group());
    }

    /**
     * @return array<string, mixed>
     */
    private function properties(): array
    {
        $traceId = Context::get(RequestJobContext::TRACE_ID);

        return is_string($traceId) ? [RequestJobContext::TRACE_ID => $traceId] : [];
    }
}
