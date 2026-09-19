<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Actions;

use Misaf\VendraNewsletter\Models\NewsletterSubscriber;

final class ResubscribeNewsletterSubscriberAction
{
    public function execute(NewsletterSubscriber $subscriber): NewsletterSubscriber
    {
        if (! $subscriber->isSubscribed()) {
            $subscriber->forceFill([
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ])->save();
        }

        return $subscriber;
    }
}
