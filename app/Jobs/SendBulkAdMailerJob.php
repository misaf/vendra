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
use Misaf\User\Models\User;
use Throwable;

final class SendBulkAdMailerJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $subject,
        public string|Stringable $description,
    ) {}

    /**
     * @return void
     */
    public function handle(): void
    {
        $chunkSize = 100;

        User::whereNotNull('email_verified_at')
            ->select('id', 'email')
            ->chunkById($chunkSize, function (Collection $users): void {
                $this->processChunk($users);
            });
    }

    /**
     * @param Collection $users
     * @return void
     */
    private function processChunk(Collection $users): void
    {
        $jobs = $users->map(
            fn($user) => (new SendAdMailerJob(
                email: $user->email,
                subject: $this->subject,
                description: $this->description,
            ))->onQueue('bulk-email'),
        );

        Bus::batch($jobs->all())
            ->name('Send Bulk Ad Emails')
            ->before(fn(Batch $batch) => Log::info("Batch {$batch->id} is starting."))
            ->progress(fn(Batch $batch) => Log::info("Batch {$batch->id} is making progress."))
            ->then(fn(Batch $batch) => Log::info("Batch {$batch->id} completed successfully."))
            ->catch(fn(Batch $batch, Throwable $e) => Log::error("Batch {$batch->id} failed: {$e->getMessage()}"))
            ->finally(fn(Batch $batch) => Log::info("Batch {$batch->id} has finished processing."))
            ->dispatchAfterResponse();
    }
}
