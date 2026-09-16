<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Concerns;

use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

trait ValidatesUniquenessWithinTenant
{
    use InteractsWithTranslatedFormFields;

    /**
     * Require a value unique within the current tenant, ignoring trashed rows. Translatable
     * columns validate against the translation of the form's active locale.
     */
    public function uniqueWithinTenant(bool $perLocale = false): static
    {
        $name = $this->getName();

        return $this->unique(
            column: $perLocale ? fn (Livewire $livewire): string => "{$name}->".self::activeFormLocale($livewire) : null,
            modifyRuleUsing: fn (Unique $rule): Unique => TenantAwareness::constrainUniqueRule($rule)->withoutTrashed(),
        );
    }
}
