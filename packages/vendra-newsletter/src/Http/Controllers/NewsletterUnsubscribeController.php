<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Http\Controllers;

use Illuminate\Contracts\View\View;
use Misaf\VendraNewsletter\Actions\UnsubscribeNewsletterSubscriberAction;
use Misaf\VendraNewsletter\Models\NewsletterSubscriber;

final class NewsletterUnsubscribeController
{
    public function __invoke(string $token): View
    {
        $subscriber = NewsletterSubscriber::query()
            ->where('unsubscribe_token', $token)
            ->first();

        if ($subscriber instanceof NewsletterSubscriber) {
            resolve(UnsubscribeNewsletterSubscriberAction::class)->execute($subscriber);
        }

        return view('vendra-newsletter::unsubscribe', [
            'subscriber' => $subscriber,
        ]);
    }
}
