<?php

declare(strict_types=1);

namespace Misaf\VendraConsole\Console\Commands\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Misaf\VendraUser\Models\User;

trait IdentifiesConsoleUser
{
    private function givenEmail(): ?string
    {
        $email = $this->option('email');

        return is_string($email) ? Str::lower(mb_trim($email)) : null;
    }

    private function givenUsername(): ?string
    {
        $username = $this->option('username');

        return is_string($username) ? mb_trim($username) : null;
    }

    /**
     * Find the tenantless user every supplied identifier names.
     *
     * Callers validate that each identifier exists on its own, so a null result
     * means the email and the username name different users.
     *
     * @throws InvalidArgumentException
     */
    private function findIdentifiedUser(?string $email, ?string $username): ?User
    {
        // Without an identifier the lookup would match every tenantless user.
        throw_if($email === null && $username === null, InvalidArgumentException::class, 'An email or a username is required to identify a console user.');

        return User::query()->tenantless()
            ->when($email !== null, fn (Builder $query): Builder => $query->where('email', $email))
            ->when($username !== null, fn (Builder $query): Builder => $query->where('username', $username))
            ->first();
    }
}
