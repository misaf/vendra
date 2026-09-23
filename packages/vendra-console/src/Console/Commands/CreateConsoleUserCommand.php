<?php

declare(strict_types=1);

namespace Misaf\VendraConsole\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Validator;
use Misaf\VendraConsole\Actions\CreateConsoleUserAction;
use Misaf\VendraConsole\Console\Commands\Concerns\IdentifiesConsoleUser;
use Misaf\VendraConsole\Console\Commands\Concerns\ReadsGivenPassword;
use Misaf\VendraConsole\Models\Console;
use Misaf\VendraConsole\Support\ConsoleCredentials;
use Misaf\VendraUser\Models\User;
use Misaf\VendraUser\Support\UserRules;

#[Description('Create a console user')]
#[Signature('vendra-console:user-create
        {--username= : Username for the new console user}
        {--email= : Email address for the new console user}
        {--password= : Password to set, asked for without echo when given no value; a strong one is generated when omitted}')]
final class CreateConsoleUserCommand extends Command
{
    use IdentifiesConsoleUser;
    use ReadsGivenPassword;

    public function handle(): int
    {
        $email = $this->givenEmail();
        $username = $this->givenUsername();
        $password = $this->givenPassword() ?? UserRules::generatePassword();

        $validator = Validator::make(
            ['email' => $email, 'username' => $username, 'password' => $password],
            [
                'email' => ['required', ...UserRules::email()],
                'username' => ['bail', 'required', ...UserRules::username()],
                'password' => ['required', ...UserRules::password()],
            ],
            [
                'email.required' => 'An email is required to create a console user. Use --email.',
                'username.required' => 'A username is required to create a console user. Use --username.',
            ],
        );

        if ($validator->fails()) {
            $this->components->error($validator->errors()->first());

            return self::FAILURE;
        }

        $userWithEmail = User::query()->tenantless()->identifiedBy(email: $email)->first();
        $userWithUsername = User::query()->tenantless()->identifiedBy(username: $username)->first();

        if ($userWithEmail !== null) {
            return $this->reportExistingUser($userWithEmail, "The email [{$email}] already belongs to a tenantless user.");
        }

        if ($userWithUsername !== null) {
            return $this->reportExistingUser($userWithUsername, "The username [{$username}] already belongs to [{$userWithUsername->email}].");
        }

        try {
            $user = resolve(CreateConsoleUserAction::class)->execute($username, $email, $password);
        } catch (UniqueConstraintViolationException) {
            $this->components->error("The username [{$username}] or the email [{$email}] is already taken. Choose another.");

            return self::FAILURE;
        }

        ConsoleCredentials::report($this, 'Console user created.', $user->email, $password);

        return self::SUCCESS;
    }

    private function reportExistingUser(User $user, string $message): int
    {
        $this->components->error($message);

        if (Console::query()->active()->forUser($user)->exists()) {
            $this->line("  [{$user->email}] already has console access. Use vendra-console:user-password to issue a new password.");
        } else {
            $this->line("  Use vendra-console:user-grant to grant [{$user->email}] console access.");
        }

        return self::FAILURE;
    }
}
