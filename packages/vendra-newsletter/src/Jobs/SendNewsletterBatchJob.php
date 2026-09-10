<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Jobs;

use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;
use Misaf\VendraNewsletter\Context\NewsletterContextKeys;
use Misaf\VendraSupport\Context\RequestJobContext;

final class SendNewsletterBatchJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries;

    public int $timeout;

    /**
     * @param  list<int>  $subscriberIds
     */
    public function __construct(
        public readonly int $newsletterId,
        public readonly array $subscriberIds,
    ) {
        $connection = Config::string('vendra-newsletter.queue.connection', '');

        $this->onConnection($connection === '' ? null : $connection);
        $this->onQueue(Config::string('vendra-newsletter.queue.name', 'default'));
        $this->tries = Config::integer('vendra-newsletter.queue.tries', 3);
        $this->timeout = Config::integer('vendra-newsletter.queue.timeout', 30);
    }

    public function handle(): void
    {
        new RequestJobContext(
            traceId: RequestJobContext::resolveTraceId(),
            operation: 'newsletter_batch',
            metadata: [NewsletterContextKeys::NEWSLETTER_ID => $this->newsletterId],
        )->scope(function (): void {
            foreach ($this->subscriberIds as $subscriberId) {
                dispatch(new SendNewsletterEmailJob($this->newsletterId, $subscriberId));
            }
        });
    }

    public function uniqueId(): string
    {
        return $this->newsletterId.':'.hash('sha256', implode(',', $this->subscriberIds));
    }
}
