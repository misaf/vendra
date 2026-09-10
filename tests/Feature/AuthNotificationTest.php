<?php

declare(strict_types=1);

use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Misaf\VendraAuthifyLog\Notifications\LoginNotification;
use Misaf\VendraConsole\Models\ConsoleUser;
use Misaf\VendraReseller\Models\ResellerUser;
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

it('builds verification emails for console and reseller users', function (): void {
    foreach ([ConsoleUser::class, ResellerUser::class] as $userClass) {
        $user = new $userClass([
            'username' => 'nina',
            'email' => 'nina@local',
        ]);
        $notification = new VerifyEmailNotification;
        $notification->url = 'https://vendra.test/verify-email';

        $message = $notification->toMail($user);

        expect($message->actionUrl)->toBe('https://vendra.test/verify-email')
            ->and($message->greeting)->toContain('nina');
    }
});

it('builds password reset emails for console and reseller users', function (): void {
    foreach ([ConsoleUser::class, ResellerUser::class] as $userClass) {
        $user = new $userClass([
            'username' => 'nina',
            'email' => 'nina@local',
        ]);
        $notification = new ResetPasswordNotification;
        $notification->url = 'https://vendra.test/reset-password';

        $message = $notification->toMail($user);

        expect($message->actionUrl)->toBe('https://vendra.test/reset-password')
            ->and($message->introLines)->toContain(__('mail.reset_password.greeting', ['user' => 'nina']));
    }
});
