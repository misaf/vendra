<?php

declare(strict_types=1);

namespace Misaf\VendraConsole\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Misaf\VendraConsole\Actions\CreateConsoleUserAction;
use Misaf\VendraConsole\Actions\GrantConsoleAccessAction;
use Misaf\VendraConsole\Actions\RevokeConsoleUserAction;
use Misaf\VendraConsole\Exceptions\LastConsoleUserException;
use Misaf\VendraConsole\Models\Console;
use Misaf\VendraConsole\Support\ConsoleAddress;
use Misaf\VendraConsole\Support\ConsoleCredentials;
use Misaf\VendraUser\Actions\UpdateUserPasswordAction;
use Misaf\VendraUser\Models\User;
use Misaf\VendraUser\Support\UserRules;

#[Description('Create a console user, issue a new password to an existing one, or revoke console access')]
#[Signature('vendra-console:user
        {--username= : Username for a new console user; prompts when omitted}
        {--email= : Email address for the console user; defaults to console@<app host>}
        {--password= : Password to set; a strong one is generated when omitted}
        {--revoke : Revoke console access from the user given by --email}
        {--force : Run without confirmation}')]
final class ConsoleUserCommand extends Command
{
    public function handle(): int
    {
        $email = $this->resolveEmail();

        if ($this->option('revoke') === true) {
            return $this->revoke($email);
        }

        if ($email === null) {
            return self::FAILURE;
        }

        $password = $this->resolvePassword();

        if ($password === null) {
            return self::FAILURE;
        }

        $user = User::query()->tenantless()->where('email', $email)->first();

        if ($user === null) {
            $username = $this->resolveUsername();

            if ($username === null) {
                return self::FAILURE;
            }

            try {
                $user = resolve(CreateConsoleUserAction::class)->execute($username, $email, $password);
            } catch (UniqueConstraintViolationException) {
                $this->components->error('The username or email is already taken. Choose another.');

                return self::FAILURE;
            }

            ConsoleCredentials::report($this, 'Console user created.', $user->email, $password);

            return self::SUCCESS;
        }

        $hasConsoleAccess = Console::query()->active()->forUser($user)->exists();

        if (! $this->confirmChangesToExistingUser($email, $hasConsoleAccess, is_string($this->option('password')))) {
            return self::FAILURE;
        }

        $user = DB::transaction(function () use ($user, $password): User {
            resolve(GrantConsoleAccessAction::class)->execute($user);

            return resolve(UpdateUserPasswordAction::class)->execute($user, $password);
        });

        ConsoleCredentials::report(
            $this,
            $hasConsoleAccess ? 'Console user password updated.' : 'Console access granted and password updated.',
            $user->email,
            $password,
        );

        return self::SUCCESS;
    }

    private function resolveUsername(): ?string
    {
        $username = $this->option('username');

        if ($username === null && $this->input->isInteractive()) {
            $username = $this->ask('Username');
        }

        $username = is_string($username) ? mb_trim($username) : null;
        $validator = Validator::make(
            ['username' => $username],
            ['username' => ['bail', 'required', ...UserRules::username(), UserRules::unique('username')]],
            ['username.required' => 'A username is required to create a console user. Use --username.'],
        );

        if ($validator->fails()) {
            $this->components->error($validator->errors()->first());

            return null;
        }

        return $username;
    }

    private function resolveEmail(): ?string
    {
        $email = $this->option('email');
        $email = is_string($email) ? Str::lower(mb_trim($email)) : null;

        if ($this->option('revoke') === true) {
            return $email === '' ? null : $email;
        }

        $email ??= ConsoleAddress::defaultEmail();
        $validator = Validator::make(
            ['email' => $email],
            ['email' => ['bail', 'required', 'email']],
            ['email.required' => 'The --email option cannot be blank.'],
        );

        if ($validator->fails()) {
            $this->components->error($validator->errors()->first());

            return null;
        }

        return $email;
    }

    private function resolvePassword(): ?string
    {
        $password = $this->option('password');
        $password = is_string($password) ? $password : UserRules::generatePassword();
        $validator = Validator::make(
            ['password' => $password],
            ['password' => ['required', ...UserRules::password()]],
        );

        if ($validator->fails()) {
            $this->components->error($validator->errors()->first());

            return null;
        }

        return $password;
    }

    private function revoke(?string $email): int
    {
        if ($email === null) {
            $this->components->error('The --revoke option requires --email.');

            return self::FAILURE;
        }

        $user = User::query()->tenantless()->where('email', $email)->first();

        if ($user === null) {
            $this->components->error("No tenantless user has the email [{$email}].");

            return self::FAILURE;
        }

        try {
            $revoked = resolve(RevokeConsoleUserAction::class)->execute($user);
        } catch (LastConsoleUserException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info($revoked
            ? "Console access revoked from [{$user->email}]."
            : "[{$user->email}] has no console access.");

        return self::SUCCESS;
    }

    private function confirmChangesToExistingUser(string $email, bool $hasConsoleAccess, bool $passwordGiven): bool
    {
        if ($this->option('force') === true) {
            return true;
        }

        if (! $hasConsoleAccess) {
            if ($this->confirm("[{$email}] is an existing user without console access. Grant console access and issue a new password?")) {
                return true;
            }

            $this->components->error('No console access was granted.');

            return false;
        }

        if ($passwordGiven || $this->confirm("[{$email}] is already a console user. Issue a new password?")) {
            return true;
        }

        $this->components->error('The password was not changed.');

        return false;
    }
}
