<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;
use Throwable;

final class SendCustomBulkAdMailerJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $subject,
        public string|Stringable $description,
        public ?int $count = null,
        public ?int $fromRecord = null,
        public ?int $toRecord = null,
    ) {}

    /**
     * @return void
     */
    public function handle(): void
    {
        // Step 1: Retrieve all records into a collection
        $newsletterSubscribers = NewsletterSubscriberService::query()
            ->whereNotNull('subscribed_at')
            ->whereNull('unsubscribed_at')
            ->withoutTags(['Hard Bounced', 'Complained']) // Exclude hard bounces and complaints
            ->orderBy('id', 'ASC')
            ->get(['id', 'email'])
            ->values();

        // Step 2: Filter the collection based on indices and shuffle if needed
        $filteredSubscribers = $this->filterSubscribers($newsletterSubscribers);

        // Step 3: Process the filtered collection
        $this->processChunk($filteredSubscribers);
    }

    /**
     * @param Collection $subscribers
     * @return Collection
     */
    private function filterSubscribers(Collection $subscribers): Collection
    {
        if (null !== $this->fromRecord && null === $this->toRecord && null === $this->count) {
            // Case: Only 'fromRecord' is provided
            return $subscribers->slice($this->fromRecord - 1)->values();
        }

        if (null === $this->fromRecord && null !== $this->toRecord && null === $this->count) {
            // Case: Only 'toRecord' is provided
            return $subscribers->take($this->toRecord)->values();
        }

        if (null === $this->fromRecord && null === $this->toRecord && null !== $this->count) {
            // Case: Only 'count' is provided
            return $subscribers->count() <= $this->count
                ? $subscribers
                : $subscribers->shuffle()->take($this->count)->values();
        }

        if (null !== $this->fromRecord && null !== $this->toRecord && null === $this->count) {
            // Case: Both 'fromRecord' and 'toRecord' are provided
            $length = $this->toRecord - $this->fromRecord + 1;

            return $subscribers->slice($this->fromRecord - 1, $length)->values();
        }

        if (null !== $this->fromRecord && null !== $this->toRecord && null !== $this->count) {
            // Case: 'fromRecord', 'toRecord', and 'count' are all provided
            $length = $this->toRecord - $this->fromRecord + 1;
            $filtered = $subscribers->slice($this->fromRecord - 1, $length);

            return $filtered->count() > $this->count
                ? $filtered->shuffle()->take($this->count)->values()
                : $filtered->values();
        }

        // Default: Return the original collection if no criteria are matched
        return $subscribers->values();
    }

    /**
     * @param Collection $newsletterSubscribers
     * @return void
     */
    private function processChunk(Collection $newsletterSubscribers): void
    {
        $jobs = $newsletterSubscribers->map(function ($subscriber): SendAdMailerJob {
            return new SendAdMailerJob(
                email: $subscriber->email,
                subject: $this->subject,
                description: $this->description,
            );
        });

        $this->createBatch($jobs->toArray());
    }

    /**
     * @param array $jobs
     * @return void
     */
    private function createBatch(array $jobs): void
    {
        Bus::batch($jobs)
            ->name('Send Custom Bulk Ad Emails')
            ->before(fn(Batch $batch) => Log::info("Batch {$batch->id} is starting."))
            ->progress(fn(Batch $batch) => Log::info("Batch {$batch->id} is making progress."))
            ->then(fn(Batch $batch) => Log::info("Batch {$batch->id} completed successfully."))
            ->catch(fn(Batch $batch, Throwable $e) => Log::error("Batch {$batch->id} failed: {$e->getMessage()}"))
            ->finally(fn(Batch $batch) => Log::info("Batch {$batch->id} has finished processing."))
            ->onQueue('bulk-email')
            ->dispatchAfterResponse();
    }
}
