<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Misaf\VendraNewsletter\Models\NewsletterSubscriber;

final class SubscribeNewsletterSubscriberAction
{
    /**
     * @param  array{email: string, name?: string|null}  $data
     */
    public function execute(array $data): NewsletterSubscriber
    {
        return DB::transaction(function () use ($data): NewsletterSubscriber {
            $subscriber = NewsletterSubscriber::query()
                ->withTrashed()
                ->where('email', Arr::get($data, 'email'))
                ->lockForUpdate()
                ->first();

            if (! $subscriber instanceof NewsletterSubscriber) {
                return NewsletterSubscriber::query()->create($data);
            }

            if (! $subscriber->trashed()) {
                return $subscriber;
            }

            $subscriber->fill($data);
            $subscriber->forceFill([
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]);
            $subscriber->restore();

            return $subscriber;
        });
    }
}
