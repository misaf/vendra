<?php

declare(strict_types=1);

namespace Misaf\VendraConsole\Console\Commands\Concerns;

trait ReadsGivenPassword
{
    /**
     * Ask for the password without echoing it when --password is given without a value.
     *
     * This keeps it out of shell history and the process list. An empty answer, or a
     * run without interaction, yields an empty string for the password rules to reject.
     */
    private function givenPassword(): ?string
    {
        $password = $this->option('password');

        if (is_string($password)) {
            return $password;
        }

        if (! $this->input->hasParameterOption('--password', true)) {
            return null;
        }

        $password = $this->input->isInteractive() ? $this->secret('Password') : null;

        return is_string($password) ? $password : '';
    }
}
