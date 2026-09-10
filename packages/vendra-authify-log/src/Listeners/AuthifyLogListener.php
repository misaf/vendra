<?php

declare(strict_types=1);

namespace Misaf\VendraAuthifyLog\Listeners;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\CurrentDeviceLogout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Validated;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Redis;
use Misaf\LaravelAuthifyLog\Enums\AuthifyLogActionEnum;
use Misaf\VendraAuthifyLog\Notifications\LoginNotification;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraSupport\Tenancy\TenantSchema;

class AuthifyLogListener
{
    public function handleAuthenticated(Authenticated $event): void
    {
        $this->store(AuthifyLogActionEnum::Authenticated, $event);
    }

    public function handleAttempting(Attempting $event): void
    {
        $this->store(AuthifyLogActionEnum::Attempting, $event);
    }

    public function handleCurrentDeviceLogout(CurrentDeviceLogout $event): void
    {
        $this->store(AuthifyLogActionEnum::CurrentDeviceLogout, $event);
    }

    public function handleFailed(Failed $event): void
    {
        $this->store(AuthifyLogActionEnum::Failed, $event);
    }

    public function handleLockout(Lockout $event): void
    {
        $this->store(AuthifyLogActionEnum::Lockout, $event);
    }

    public function handleOtherDeviceLogout(OtherDeviceLogout $event): void
    {
        $this->store(AuthifyLogActionEnum::OtherDeviceLogout, $event);
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        $this->store(AuthifyLogActionEnum::PasswordReset, $event);
    }

    public function handleRegistered(Registered $event): void
    {
        $this->store(AuthifyLogActionEnum::Registered, $event);
    }

    public function handleLogout(Logout $event): void
    {
        $this->store(AuthifyLogActionEnum::Logout, $event);
    }

    public function handleValidated(Validated $event): void
    {
        $this->store(AuthifyLogActionEnum::Validated, $event);
    }

    public function handleVerified(Verified $event): void
    {
        $this->store(AuthifyLogActionEnum::Verified, $event);
    }

    public function handleLogin(Login $event): void
    {
        /** @var object{hasVerifiedEmail: callable, notify: callable} $user */
        $user = $event->user;

        if ($user->hasVerifiedEmail()) {
            $user->notify(new LoginNotification);
        }

        $this->store(AuthifyLogActionEnum::Login, $event);
    }

    private function store(AuthifyLogActionEnum $action, object $event, Request $request): void
    {
        $userId = isset($event->user) ? $event->user->id : null;

        $timestamp = Date::now()->toDateTimeString();
        $logEntry = [
            TenantSchema::column() => TenantAwareness::currentId(),
            'user_id' => $userId,
            'action' => $action->value,
            'ip_address' => $request->ip(),
            'ip_country' => $request->header('CF-IPCountry') ?? 'XX',
            'user_agent' => $request->userAgent(),
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];

        $authifyTransaction = Redis::connection('authify_log')->rpush('entries', json_encode($logEntry));
        Redis::connection('authify_log_channel')->publish('authify-log-channel', $authifyTransaction);
    }
}
