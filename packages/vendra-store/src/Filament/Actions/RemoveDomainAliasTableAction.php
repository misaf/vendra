<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Filament\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use LogicException;
use Misaf\VendraStore\Actions\RemoveStoreDomainAliasAction;
use Misaf\VendraStore\Filament\RelationManagers\DomainsRelationManager;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;

/**
 * Soft-deletes an alias row of {@see DomainsRelationManager}; the primary domain,
 * the one the store was created with, is never offered.
 *
 * Panels override {@see authorizationCallback()} to apply their own access rules.
 */
abstract class RemoveDomainAliasTableAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'removeDomainAlias';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-store::actions.remove_domain_alias'))
            ->icon(Heroicon::OutlinedMinusCircle)
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (StoreDomain $record, RelationManager $livewire): bool => $record->active
                && ! $record->is_primary
                && ! $record->trashed()
                && ! self::store($livewire)->trashed())
            ->action(function (StoreDomain $record, RelationManager $livewire, RemoveStoreDomainAliasAction $removeAlias): void {
                $removeAlias->execute(self::store($livewire), $record);

                Notification::make()
                    ->success()
                    ->title(__('vendra-store::messages.domain_alias_removed'))
                    ->send();
            });

        $authorization = $this->authorizationCallback();

        if ($authorization !== null) {
            $this->authorize($authorization);
        }
    }

    /**
     * Get the authorization gate, or null to leave the action unrestricted.
     *
     * @return (Closure(): bool)|null
     */
    protected function authorizationCallback(): ?Closure
    {
        return null;
    }

    private static function store(RelationManager $livewire): Store
    {
        $store = $livewire->getOwnerRecord();

        throw_unless($store instanceof Store, LogicException::class, 'Removing a domain alias requires a Store parent record.');

        return $store;
    }
}
