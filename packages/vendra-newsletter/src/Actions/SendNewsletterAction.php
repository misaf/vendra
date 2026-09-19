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

                    dispatch(new SendNewsletterBatchJob($lockedNewsletter->id, $ids))->afterCommit();

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
