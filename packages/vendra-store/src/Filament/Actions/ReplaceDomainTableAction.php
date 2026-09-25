<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Filament\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Misaf\VendraStore\Actions\ReplaceStoreDomainAction;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraSupport\Exceptions\EntitlementExceededException;

/**
 * Panels override {@see authorizationCallback()} to apply their own access rules.
 */
abstract class ReplaceDomainTableAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'replaceDomain';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-store::actions.replace_domain'))
            ->icon(Heroicon::OutlinedArrowPath)
            ->visible(fn (Store $record): bool => ! $record->trashed())
            ->schema([
                TextInput::make('domain')
                    ->label(__('vendra-store::attributes.new_domain'))
                    ->required()
                    ->maxLength(255)
                    ->rules(StoreDomain::activeDomainRules())
                    ->dehydrateStateUsing(fn (?string $state): ?string => $state === null
                        ? null
                        : StoreDomain::normalizeDomain($state)),
            ])
            ->action(function (Store $record, array $data): void {
                $domain = Arr::get($data, 'domain');

                if (! is_string($domain)) {
                    return;
                }

                try {
                    resolve(ReplaceStoreDomainAction::class)->execute($record, $domain);
                } catch (EntitlementExceededException $exception) {
                    Notification::make()
                        ->danger()
                        ->title($exception->getMessage())
                        ->send();

                    $this->halt();
                }

                Notification::make()
                    ->success()
                    ->title(__('vendra-store::messages.domain_replaced'))
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
