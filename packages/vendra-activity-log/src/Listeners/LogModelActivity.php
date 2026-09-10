<?php

declare(strict_types=1);

namespace Misaf\VendraActivityLog\Listeners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Misaf\VendraActivityLog\Providers\ActivityLogServiceProvider;
use Misaf\VendraSupport\Context\RequestJobContext;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;

/**
 * Records activity for any model implementing {@see ShouldLogActivity}.
 *
 * This listener is bound to the wildcard Eloquent lifecycle events by
 * {@see ActivityLogServiceProvider}, which
 * means models opt into logging purely by implementing the marker contract and
 * never need to depend on this package directly. It mirrors the previous
 * Spatie `LogsActivity` behaviour of logging fillable attributes (except `id`).
 */
final class LogModelActivity
{
    /**
     * Attributes that are never logged unless a model narrows the list itself.
     *
     * @var list<string>
     */
    private const array DEFAULT_EXCEPT = ['id'];

    /**
     * Handle a wildcard Eloquent model event (e.g. "eloquent.updated: App\Models\Foo").
     *
     * @param  array<int, mixed>  $payload
     */
    public function handle(string $eventName, array $payload): void
    {
        $model = Arr::get($payload, 0, null);

        if (! $model instanceof Model || ! $model instanceof ShouldLogActivity) {
            return;
        }

        if (! Config::boolean('activitylog.enabled', true)) {
            return;
        }

        $event = $this->eventFrom($eventName);

        $attributes = $this->loggableAttributes($model);

        if ($attributes === []) {
            return;
        }

        activity()
            ->performedOn($model)
            ->event($event)
            ->withChanges($this->attributeChanges($event, $model, $attributes))
            ->withProperties($this->properties())
            ->log($event);
    }

    /**
     * Extract the bare event name ("updated") from the full event ("eloquent.updated: Foo").
     */
    private function eventFrom(string $eventName): string
    {
        return Str::of($eventName)
            ->after('eloquent.')
            ->before(':')
            ->trim()
            ->value();
    }

    /**
     * @return list<string>
     */
    private function loggableAttributes(Model $model): array
    {
        return array_values(array_diff($model->getFillable(), $this->exceptFor($model)));
    }

    /**
     * Tracked model changes, stored in the dedicated `attribute_changes` column
     * (spatie/laravel-activitylog v5 keeps these separate from `properties`).
     *
     * @param  list<string>  $attributes
     * @return array<string, mixed>
     */
    private function attributeChanges(string $event, Model $model, array $attributes): array
    {
        $new = [];

        foreach ($attributes as $attribute) {
            $new[$attribute] = $model->getAttribute($attribute);
        }

        if ($event === 'updated') {
            $old = [];

            foreach ($attributes as $attribute) {
                $old[$attribute] = $model->getOriginal($attribute);
            }

            return ['attributes' => $new, 'old' => $old];
        }

        if ($event === 'deleted') {
            return ['attributes' => $new, 'old' => $new];
        }

        return ['attributes' => $new];
    }

    /**
     * Custom, non-change metadata stored in the `properties` column.
     *
     * @return array<string, mixed>
     */
    private function properties(): array
    {
        $properties = [];

        $traceId = Context::get(RequestJobContext::TRACE_ID);

        if (is_string($traceId)) {
            $properties[RequestJobContext::TRACE_ID] = $traceId;
        }

        return $properties;
    }

    /**
     * @return list<string>
     */
    private function exceptFor(Model $model): array
    {
        if (method_exists($model, 'activityLogExcept')) {
            /** @var list<string> $except */
            $except = $model->activityLogExcept();

            return $except;
        }

        return self::DEFAULT_EXCEPT;
    }
}
