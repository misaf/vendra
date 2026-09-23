<?php

declare(strict_types=1);

namespace Misaf\VendraConsole\Console\Commands\Concerns;

use Illuminate\Support\Str;

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
}
