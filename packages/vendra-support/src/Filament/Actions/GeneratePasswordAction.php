<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Actions;

use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraSupport\Filament\Support\PasswordGenerator;

final class GeneratePasswordAction extends Action
{
    protected int $length = 10;

    protected string $field = 'password';

    protected ?string $confirmationField = null;

    public static function getDefaultName(): ?string
    {
        return 'generatePassword';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('vendra-support::forms.random_password'));
        $this->icon(Heroicon::OutlinedShieldCheck);
        $this->iconPosition(fn (): IconPosition => app()->isLocale('fa') ? IconPosition::After : IconPosition::Before);
        $this->disabled(fn (string $operation): bool => $operation === 'view');

        $this->action(function (Set $set): void {
            $password = PasswordGenerator::generate($this->length);

            $set($this->field, $password);

            if ($this->confirmationField !== null) {
                $set($this->confirmationField, $password);
            }
        });
    }

    public function length(int $length): static
    {
        $this->length = $length;

        return $this;
    }

    public function field(string $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function confirmationField(?string $confirmationField): static
    {
        $this->confirmationField = $confirmationField;

        return $this;
    }
}
