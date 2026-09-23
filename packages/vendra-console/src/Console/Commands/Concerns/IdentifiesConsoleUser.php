<?php

declare(strict_types=1);

namespace Misaf\VendraConsole\Console\Commands\Concerns;

use Illuminate\Support\Str;

trait IdentifiesConsoleUser
{
    private function givenEmail(): ?string
    {
        $email = $this->option('email');

        return is_string($email) ? $this->normalizeEmail($email) : null;
    }

    private function givenUsername(): ?string
    {
        $username = $this->option('username');

        return is_string($username) ? $this->normalizeUsername($username) : null;
    }

    private function normalizeEmail(string $email): string
    {
        return Str::lower(mb_trim($email));
    }

    private function normalizeUsername(string $username): string
    {
        return mb_trim($username);
    }
}
