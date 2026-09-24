<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Filament\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Misaf\VendraStore\Actions\MakeStoreDomainPrimaryAction;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;

/**
 * Panels override {@see authorizationCallback()} to apply their own access rules.
 */
abstract class MakeDomainPrimaryTableAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'makeDomainPrimary';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-store::actions.make_domain_primary'))
            ->icon(Heroicon::OutlinedStar)
            ->visible(fn (Store $record): bool => ! $record->trashed() && $record->aliasDomains->isNotEmpty())
            ->schema([
                Select::make('domain')
                    ->label(__('vendra-store::attributes.alias_domain'))
                    ->options(fn (Store $record): array => $record->aliasDomains->pluck('name', 'id')->all())
                    ->required(),
            ])
            ->action(function (Store $record, array $data): void {
                $domain = $record->aliasDomains()->find(Arr::get($data, 'domain'));

                if (! $domain instanceof StoreDomain) {
                    return;
                }

                resolve(MakeStoreDomainPrimaryAction::class)->execute($record, $domain);

                Notification::make()
                    ->success()
                    ->title(__('vendra-store::messages.domain_made_primary'))
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
}
