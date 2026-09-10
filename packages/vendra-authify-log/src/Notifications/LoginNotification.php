<?php

declare(strict_types=1);

namespace Misaf\VendraAuthifyLog\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Misaf\LaravelAuthifyLog\Contracts\HasUsername;
use RuntimeException;

class LoginNotification extends Notification implements ShouldQueueAfterCommit
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue('transactional-email');
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        throw_unless($notifiable instanceof HasUsername, RuntimeException::class, 'Notifiable must implement HasUsername to receive LoginNotification.');

        $username = $notifiable->getAuthifyLogUsername();
        $resetPasswordUrl = route('filament.panel-user.auth.password-reset.request');

        return (new MailMessage)
            ->subject(__('vendra-authify-log::successfull-login-notification.login_notification'))
            ->greeting(__('vendra-authify-log::successfull-login-notification.hello_user', ['user' => $username]))
            ->line(__('vendra-authify-log::successfull-login-notification.we_noticed_that_your_account_was_accessed_on_our_website'))
            ->line(__('vendra-authify-log::successfull-login-notification.if_this_was_you_no_further_action_is_required'))
            ->line(__('vendra-authify-log::successfull-login-notification.if_this_was_not_you_please_reset_your_password_immediately_to_secure_your_account'))
            ->action(__('vendra-authify-log::successfull-login-notification.reset_your_password'), $resetPasswordUrl)
            ->line(__('vendra-authify-log::successfull-login-notification.thank_you_for_trusting_our_application'))
            ->salutation(__('vendra-authify-log::successfull-login-notification.best_regards')."\n".Config::string('app.name'));
    }
}
