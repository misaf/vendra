<?php

declare(strict_types=1);

use Illuminate\Console\Command;
use Misaf\VendraConsole\Console\Commands\Concerns\IdentifiesConsoleUser;
use Misaf\VendraUser\Models\User;

it('refuses to look up a console user without an identifier', function (): void {
    User::factory()->create(['tenant_id' => null]);

    $command = new class extends Command
    {
        use IdentifiesConsoleUser;

        public function lookUpWithoutIdentifiers(): ?User
        {
            return $this->findIdentifiedUser(null, null);
        }
    };

    expect(fn (): ?User => $command->lookUpWithoutIdentifiers())->toThrow(InvalidArgumentException::class);
});
