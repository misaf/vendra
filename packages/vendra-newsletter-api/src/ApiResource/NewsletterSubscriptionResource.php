<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletterApi\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use Misaf\VendraNewsletterApi\Http\Requests\SubscribeNewsletterRequest;
use Misaf\VendraNewsletterApi\State\SubscribeNewsletterProcessor;

#[ApiResource(
    shortName: 'NewsletterSubscription',
    denormalizationContext: ['collect_denormalization_errors' => true],
    operations: [
        new Post(
            uriTemplate: '/marketing/newsletter-subscriptions',
            status: 204,
            output: false,
            processor: SubscribeNewsletterProcessor::class,
            rules: SubscribeNewsletterRequest::class,
            middleware: ['throttle:10,1,newsletter-subscriptions:'],
            read: false,
        ),
    ],
)]
final class NewsletterSubscriptionResource
{
    public string $email = '';

    public ?string $name = null;
}
