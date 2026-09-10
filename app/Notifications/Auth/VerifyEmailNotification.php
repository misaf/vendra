<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use Filament\Auth\Notifications\VerifyEmail;
use Filament\Models\Contracts\HasName;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use InvalidArgumentException;
use Spatie\Multitenancy\Jobs\NotTenantAware;

final class VerifyEmailNotification extends VerifyEmail implements NotTenantAware, ShouldQueueAfterCommit
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue('transactional-email');
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        if (! $notifiable instanceof MustVerifyEmail || ! $notifiable instanceof HasName) {
            throw new InvalidArgumentException(sprintf(
                'Expected a verifiable Filament user, got %s.',
                get_debug_type($notifiable),
            ));
        }

        $appName = config('app.name');

        return new MailMessage()
            ->subject(__('mail.verify_email.subject'))
            ->greeting(__('mail.verify_email.greeting', ['user' => $notifiable->getFilamentName()]))
            ->line(__('mail.verify_email.line'))
            ->action(__('mail.verify_email.action'), $this->url)
            ->line(__('mail.verify_email.no_action'))
            ->salutation(__('mail.verify_email.salutation')."\n".(is_string($appName) ? $appName : ''));
    }
}
