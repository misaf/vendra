<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraLanguage\Actions\SetDefaultLanguageAction as SetDefaultLanguageDomainAction;
use Misaf\VendraLanguage\Models\Language;

final class SetDefaultLanguageTableAction
{
    public static function make(): Action
    {
        return Action::make('setDefault')
            ->action(function (Action $action, Language $record, SetDefaultLanguageDomainAction $setDefaultLanguage): void {
                $setDefaultLanguage->execute($record);
                $action->success();
            })
            ->authorize(fn (Language $record): bool => auth()->user()?->can('update', $record) ?? false)
            ->icon(Heroicon::OutlinedCheckCircle)
            ->label(__('vendra-language::actions.set_default'))
            ->requiresConfirmation()
            ->successNotificationTitle(__('vendra-language::messages.default_language_updated'))
            ->visible(fn (Language $record): bool => ! $record->is_default);
    }
}
