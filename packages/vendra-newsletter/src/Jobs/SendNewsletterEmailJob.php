<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Misaf\VendraNewsletter\Context\NewsletterContextKeys;
use Misaf\VendraNewsletter\Mail\NewsletterMail;
use Misaf\VendraNewsletter\Models\Newsletter;
use Misaf\VendraNewsletter\Models\NewsletterSubscriber;
use Misaf\VendraSupport\Context\RequestJobContext;
use RuntimeException;

final class SendNewsletterEmailJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries;

    public int $timeout;

    public function __construct(
        public readonly int $newsletterId,
        public readonly int $subscriberId,
    ) {
        $connection = Config::string('vendra-newsletter.queue.connection', '');

        $this->onConnection($connection === '' ? null : $connection);
        $this->onQueue(Config::string('vendra-newsletter.queue.name', 'default'));
        $this->tries = Config::integer('vendra-newsletter.queue.tries', 3);
        $this->timeout = Config::integer('vendra-newsletter.queue.email_timeout', 30);
    }

    public function handle(): void
    {
        (new RequestJobContext(
            traceId: RequestJobContext::resolveTraceId(),
            operation: 'newsletter_email',
            metadata: [
                NewsletterContextKeys::NEWSLETTER_ID => $this->newsletterId,
                NewsletterContextKeys::SUBSCRIBER_ID => $this->subscriberId,
            ],
        ))->scope(fn () => $this->send());
    }

    private function send(): void
    {
        $newsletter = Newsletter::query()->find($this->newsletterId);
        $subscriber = NewsletterSubscriber::query()->find($this->subscriberId);

        if (! $newsletter instanceof Newsletter || ! $subscriber instanceof NewsletterSubscriber) {
            return;
        }

        if (! $subscriber->isSubscribed()) {
            return;
        }

        DB::transaction(function () use ($newsletter, $subscriber): void {
            DB::table('newsletter_deliveries')->insertOrIgnore([
                'newsletter_id' => $newsletter->getKey(),
                'newsletter_subscriber_id' => $subscriber->getKey(),
            ]);

            $delivery = DB::table('newsletter_deliveries')
                ->where('newsletter_id', $newsletter->getKey())
                ->where('newsletter_subscriber_id', $subscriber->getKey())
                ->lockForUpdate()
                ->first(['sent_at']);

            if (! is_object($delivery)) {
                throw new RuntimeException('Unable to create the newsletter delivery receipt.');
            }

            if ($delivery->sent_at !== null) {
                return;
            }

            Mail::to($subscriber->email)->send(new NewsletterMail($newsletter, $subscriber));

            DB::table('newsletter_deliveries')
                ->where('newsletter_id', $newsletter->getKey())
                ->where('newsletter_subscriber_id', $subscriber->getKey())
                ->update(['sent_at' => now()]);
        });
    }

    public function uniqueId(): string
    {
        return $this->newsletterId.':'.$this->subscriberId;
    }
}
