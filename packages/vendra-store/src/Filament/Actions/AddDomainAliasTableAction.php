<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Filament\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use LogicException;
use Misaf\VendraStore\Actions\AddStoreDomainAliasAction;
use Misaf\VendraStore\Filament\RelationManagers\DomainsRelationManager;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraSupport\Contracts\TenantEntitlements;
use Misaf\VendraSupport\Enums\PlanLimit;
use Misaf\VendraSupport\Exceptions\EntitlementExceededException;

/**
 * Sits in the header of {@see DomainsRelationManager}. Panels override
 * {@see authorizationCallback()} to apply their own access rules.
 */
abstract class AddDomainAliasTableAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'addDomainAlias';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-store::actions.add_domain_alias'))
            ->icon(Heroicon::OutlinedPlusCircle)
            ->visible(fn (): bool => ! $this->domainStore()->trashed())
            ->disabled(fn (TenantEntitlements $entitlements): bool => ! $entitlements->canAdd(PlanLimit::DomainsPerStore, tenant: $this->domainStore()))
            ->tooltip(fn (TenantEntitlements $entitlements): ?string => $entitlements->canAdd(PlanLimit::DomainsPerStore, tenant: $this->domainStore())
                ? null
                : EntitlementExceededException::limitReached(
                    PlanLimit::DomainsPerStore,
                    $entitlements->limit(PlanLimit::DomainsPerStore, $this->domainStore()) ?? 0,
                )->getMessage())
            ->schema([
                TextInput::make('domain')
                    ->label(__('vendra-store::attributes.alias_domain'))
                    ->required()
                    ->maxLength(255)
                    ->rules(StoreDomain::activeDomainRules())
                    ->dehydrateStateUsing(fn (?string $state): ?string => $state === null
                        ? null
                        : StoreDomain::normalizeDomain($state)),
            ])
            ->action(function (array $data): void {
                $domain = Arr::get($data, 'domain');

                if (! is_string($domain)) {
                    return;
                }

                try {
                    resolve(AddStoreDomainAliasAction::class)->execute($this->domainStore(), $domain);
                } catch (EntitlementExceededException $exception) {
                    Notification::make()
                        ->danger()
                        ->title($exception->getMessage())
                        ->send();

                    $this->halt();
                }

                Notification::make()
                    ->success()
                    ->title(__('vendra-store::messages.domain_alias_added'))
                    ->send();
            });

        $authorization = $this->authorizationCallback();

        if ($authorization !== null) {
            $this->authorize($authorization);
        }
    }

    /**
     * Get the store the alias is added to: the relation manager's owner record.
     */
    protected function domainStore(): Store
    {
        $livewire = $this->getLivewire();
        $store = $livewire instanceof RelationManager ? $livewire->getOwnerRecord() : null;

        throw_unless($store instanceof Store, LogicException::class, 'Adding a domain alias requires a Store parent record.');

        return $store;
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
}
