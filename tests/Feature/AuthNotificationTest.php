<?php

declare(strict_types=1);

use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use Filament\Facades\Filament;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Misaf\VendraAuthifyLog\Notifications\LoginNotification;
use Misaf\VendraUser\Models\User;
use Spatie\Multitenancy\Jobs\NotTenantAware;

it('keeps authentication notifications independent of tenant context', function (): void {
    expect(new VerifyEmailNotification)->toBeInstanceOf(NotTenantAware::class)
        ->and(new VerifyEmailNotification)->toBeInstanceOf(ShouldQueueAfterCommit::class)
        ->and(new ResetPasswordNotification)->toBeInstanceOf(NotTenantAware::class)
        ->and(new ResetPasswordNotification)->toBeInstanceOf(ShouldQueueAfterCommit::class)
        ->and(new LoginNotification)->toBeInstanceOf(ShouldQueueAfterCommit::class);
});

it('assigns transactional email to a Horizon supervisor', function (): void {
    expect(config('horizon.defaults.supervisor-1.queue'))
        ->toContain('transactional-email');
});

it('builds verification emails for the canonical user', function (): void {
    $user = new User([
        'username' => 'nina',
        'email' => 'nina@local',
    ]);
    $notification = new VerifyEmailNotification;
    $notification->url = 'https://vendra.test/verify-email';

    $message = $notification->toMail($user);

    expect($message->actionUrl)->toBe('https://vendra.test/verify-email')
        ->and($message->greeting)->toContain('nina');
});

it('builds password reset emails for the canonical user', function (): void {
    $user = new User([
        'username' => 'nina',
        'email' => 'nina@local',
    ]);
    $notification = new ResetPasswordNotification;
    $notification->url = 'https://vendra.test/reset-password';

    $message = $notification->toMail($user);

    expect($message->actionUrl)->toBe('https://vendra.test/reset-password')
        ->and($message->introLines)->toContain(__('mail.reset_password.greeting', ['user' => 'nina']));
});

it('quotes the expiry of the broker that issued the reset link', function (): void {
    config()->set('auth.passwords.console.expire', 45);

    $user = new User([
        'username' => 'nina',
        'email' => 'nina@local',
    ]);

    Filament::setCurrentPanel(Filament::getPanel('console'));

    $platform = new ResetPasswordNotification;
    $platform->url = 'https://console.vendra.test/reset-password';

    expect($platform->broker)->toBe('console')
        ->and($platform->toMail($user)->outroLines)
        ->toContain(__('mail.reset_password.expire', ['count' => 45]));

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $web = new ResetPasswordNotification;
    $web->url = 'https://vendra.test/reset-password';

    expect($web->broker)->toBe('users')
        ->and($web->toMail($user)->outroLines)
        ->toContain(__('mail.reset_password.expire', ['count' => config('auth.passwords.users.expire')]));
});
