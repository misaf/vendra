<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use Filament\Auth\Notifications\ResetPassword;
use Filament\Facades\Filament;
use Filament\Models\Contracts\HasName;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Spatie\Multitenancy\Jobs\NotTenantAware;

final class ResetPasswordNotification extends ResetPassword implements NotTenantAware, ShouldQueueAfterCommit
{
    use Queueable;

    /**
     * Password broker the reset link was issued by.
     *
     * Platform panels (console, reseller) use their own broker and token
     * store, so the default broker's expiry would be the wrong number to
     * quote here. The notification is resolved while the panel is still
     * current, so the broker is captured now and travels with the queued
     * job.
     */
    public string $broker;

    public function __construct()
    {
        $this->onQueue('transactional-email');

        $this->broker = Filament::getCurrentPanel()?->getAuthPasswordBroker()
            ?? Config::string('auth.defaults.passwords');
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        if (! $notifiable instanceof HasName) {
            throw new InvalidArgumentException(sprintf(
                'Expected a Filament user, got %s.',
                get_debug_type($notifiable),
            ));
        }

        $expire = config('auth.passwords.'.$this->broker.'.expire');

        return new MailMessage()
            ->subject(__('mail.reset_password.subject'))
            ->line(__('mail.reset_password.greeting', ['user' => $notifiable->getFilamentName()]))
            ->line(__('mail.reset_password.line'))
            ->action(__('mail.reset_password.action'), $this->url)
            ->line(__('mail.reset_password.expire', [
                'count' => is_int($expire) ? $expire : 0,
            ]))
            ->line(__('mail.reset_password.no_action'));
    }
}
