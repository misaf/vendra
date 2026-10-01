<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletterApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Misaf\VendraNewsletter\Actions\ResubscribeNewsletterSubscriberAction;
use Misaf\VendraNewsletter\Actions\SubscribeNewsletterSubscriberAction;
use Misaf\VendraNewsletterApi\ApiResource\NewsletterSubscriptionResource;

/** @implements ProcessorInterface<NewsletterSubscriptionResource, null> */
final readonly class SubscribeNewsletterProcessor implements ProcessorInterface
{
    public function __construct(
        private SubscribeNewsletterSubscriberAction $subscribeSubscriber,
        private ResubscribeNewsletterSubscriberAction $resubscribeSubscriber,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        DB::transaction(function () use ($data): void {
            $subscriber = $this->subscribeSubscriber->execute([
                'email' => Str::lower(trim($data->email)),
                'name' => $data->name === null ? null : trim($data->name),
            ]);

            $this->resubscribeSubscriber->execute($subscriber);
        });

        return null;
    }
}
