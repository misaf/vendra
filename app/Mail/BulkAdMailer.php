<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Stringable;

final class BulkAdMailer extends Mailable
{
    public function __construct(
        public string $email,
        public string $subject2,
        public string|Stringable $description,
    ) {}

    /**
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject2,
        );
    }

    /**
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.bulk.advertising',
            with: [
                'email'       => $this->email,
                'subject'     => $this->subject2,
                'description' => $this->description,
            ],
        );
    }
}
