<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Jobs\Middleware\RateLimited;
use App\Mail\BulkAdMailer;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Stringable;
use Throwable;

final class SendAdMailerJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 20;

    public int $backoff = 10;

    public function __construct(
        public string $email,
        public string $subject,
        public string|Stringable $description,
    ) {}

    /**
     * @return void
     */
    public function handle(): void
    {
        try {
            $mailer = new BulkAdMailer(
                email: $this->email,
                subject2: $this->subject,
                description: $this->description,
            );

            Mail::to($this->email)->send($mailer);

            Log::info('Email sent successfully.', ['email' => $this->email]);
        } catch (Throwable $exception) {
            Log::error('Failed to send email.', [
                'email' => $this->email,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new RateLimited()];
    }
}
