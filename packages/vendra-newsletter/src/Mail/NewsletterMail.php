<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Misaf\VendraNewsletter\Models\Newsletter;
use Misaf\VendraNewsletter\Models\NewsletterSubscriber;

final class NewsletterMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Newsletter $newsletter,
        public readonly NewsletterSubscriber $subscriber,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->newsletter->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'vendra-newsletter::mail.newsletter',
            with: [
                'newsletter' => $this->newsletter,
                'subscriber' => $this->subscriber,
                'unsubscribeUrl' => route('vendra-newsletter.unsubscribe', [
                    'token' => $this->subscriber->unsubscribe_token,
                ]),
            ],
        );
    }
}
