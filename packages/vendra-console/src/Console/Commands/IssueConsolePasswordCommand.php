<?php

declare(strict_types=1);

namespace Misaf\VendraConsole\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Misaf\VendraConsole\Console\Commands\Concerns\IdentifiesConsoleUser;
use Misaf\VendraConsole\Console\Commands\Concerns\ReadsGivenPassword;
use Misaf\VendraConsole\Models\Console;
use Misaf\VendraConsole\Support\ConsoleCredentials;
use Misaf\VendraUser\Actions\UpdateUserPasswordAction;
use Misaf\VendraUser\Models\User;
use Misaf\VendraUser\Support\UserRules;

use function Laravel\Prompts\confirm;

#[Description('Issue a new password to a console user')]
#[Signature('vendra-console:user-password
        {--username= : Username of the console user}
        {--email= : Email address of the console user; defaults to the vendra-console.default_email config value}
        {--password= : Password to set, asked for without echo when given no value; a strong one is generated when omitted}
        {--force : Run without confirmation}')]
final class IssueConsolePasswordCommand extends Command
{
    use IdentifiesConsoleUser;
    use ReadsGivenPassword;

    public function handle(): int
    {
        $email = $this->givenEmail();
        $username = $this->givenUsername();
        $givenPassword = $this->givenPassword();
        $password = $givenPassword ?? UserRules::generatePassword();

        if ($email === null && $username === null) {
            $email = Config::string('vendra-console.default_email');
        }

        $validator = Validator::make(
            ['email' => $email, 'username' => $username, 'password' => $password],
            [
                'email' => ['bail', 'exclude_if:email,null', 'filled', ...UserRules::email(), UserRules::exists('email')],
                'username' => ['bail', 'exclude_if:username,null', 'filled', ...UserRules::username(), UserRules::exists('username')],
                'password' => ['required', ...UserRules::password()],
            ],
            [
                'email.exists' => 'No tenantless user has the email [:input]. Use vendra-console:user-create to create one.',
                'username.exists' => 'No tenantless user has the username [:input]. Use vendra-console:user-create to create one.',
                'email.filled' => 'The --email option cannot be blank.',
            ],
        );

        if ($validator->fails()) {
            $this->components->error($validator->errors()->first());

            return self::FAILURE;
        }

        // Each identifier exists on its own, so no user matching both means they name different users.
        $user = User::query()->tenantless()->identifiedBy($email, $username)->first();

        if ($user === null) {
            $this->components->error('The --email and --username options identify different users.');

            return self::FAILURE;
        }

        if (! Console::query()->active()->forUser($user)->exists()) {
            $this->components->error("[{$user->email}] has no console access.");
            $this->line('  Use vendra-console:user-grant to grant it.');

            return self::FAILURE;
        }

        $skipConfirmation = $this->option('force') === true || $givenPassword !== null;

        if (! $skipConfirmation && ! $this->input->isInteractive()) {
            $this->components->error('The password was not changed.');
            $this->line('  Pass --force, or give --password, to issue a password without a prompt.');

            return self::FAILURE;
        }

        if (! $skipConfirmation && ! confirm("[{$user->email}] is already a console user. Issue a new password?", default: false)) {
            $this->components->error('The password was not changed.');

            return self::FAILURE;
        }

        $user = resolve(UpdateUserPasswordAction::class)->execute($user, $password);

        ConsoleCredentials::report($this, 'Console user password updated.', $user->email, $password);

        return self::SUCCESS;
    }
}
