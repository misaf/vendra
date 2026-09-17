<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Tables\Columns;

use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

final class IsActiveToggleColumn extends ToggleColumn
{
    public static function getDefaultName(): string
    {
        return 'active';
    }

    /**
     * Filament only skips an inline update when the column is disabled; it never
     * consults the record's policy. Deny the toggle unless the panel user may
     * update the record, and leave policy-less models to their panel's access gate.
     */
    public function isDisabled(): bool
    {
        return parent::isDisabled() || ! $this->canUpdateRecord();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-support::attributes.active'))
            ->onIcon(Heroicon::Bolt);
    }

    private function canUpdateRecord(): bool
    {
        $record = $this->getRecord();

        if (! $record instanceof Model) {
            return true;
        }

        $policy = Gate::getPolicyFor($record);

        if ($policy === null || ! method_exists($policy, 'update')) {
            return true;
        }

        return Gate::forUser(Filament::getCurrentOrDefaultPanel()?->auth()->user())->allows('update', $record);
    }
}
