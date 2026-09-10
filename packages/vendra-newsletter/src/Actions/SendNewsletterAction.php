<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Misaf\VendraNewsletter\Enums\NewsletterStatusEnum;
use Misaf\VendraNewsletter\Jobs\SendNewsletterBatchJob;
use Misaf\VendraNewsletter\Models\Newsletter;
use Misaf\VendraNewsletter\Models\NewsletterSubscriber;

final class SendNewsletterAction
{
    /**
     * Fan the newsletter out to every subscribed recipient in tenant-scoped
     * chunks, then mark it as sent. Returns the number of recipients queued.
     */
    public function execute(Newsletter $newsletter): int
    {
        return DB::transaction(function () use ($newsletter): int {
            $lockedNewsletter = Newsletter::query()
                ->lockForUpdate()
                ->find($newsletter->id);

            if (! $lockedNewsletter instanceof Newsletter || $lockedNewsletter->status === NewsletterStatusEnum::Sent) {
                return 0;
            }

            $chunkSize = Config::integer('vendra-newsletter.batch_chunk_size', 100);
            $queued = 0;

            NewsletterSubscriber::query()
                ->subscribed()
                ->select('id')
                ->chunkById($chunkSize, function (Collection $subscribers) use ($lockedNewsletter, &$queued): void {
                    /** @var list<int> $ids */
                    $ids = $subscribers->modelKeys();

                    SendNewsletterBatchJob::dispatch($lockedNewsletter->id, $ids)->afterCommit();

                    $queued += count($ids);
                });

            $lockedNewsletter->forceFill([
                'status' => NewsletterStatusEnum::Sent,
                'sent_at' => now(),
            ])->save();

            return $queued;
        });
    }
}
