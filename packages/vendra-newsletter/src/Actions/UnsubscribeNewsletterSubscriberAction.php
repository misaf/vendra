<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Actions;

use Misaf\VendraNewsletter\Models\NewsletterSubscriber;

final class UnsubscribeNewsletterSubscriberAction
{
    public function execute(NewsletterSubscriber $subscriber): NewsletterSubscriber
    {
        if ($subscriber->isSubscribed()) {
            $subscriber->forceFill(['unsubscribed_at' => now()])->save();
        }

        return $subscriber;
    }
}
