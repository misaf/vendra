<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use Filament\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

final class ResetPasswordNotification extends ResetPassword implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue('transactional-email');
    }
    /**
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(__('Reset Password Notification'))
            ->line(__('Dear :user,', ['user' => $notifiable->username]))
            ->line(__('You are receiving this email because we received a password reset request for your account.'))
            ->action(__('Reset Password'), $this->url)
            ->line(__('This password reset link will expire in :count minutes.', [
                'count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire'),
            ]))
            ->line(__('If you did not request a password reset, no further action is required.'));
    }
}
