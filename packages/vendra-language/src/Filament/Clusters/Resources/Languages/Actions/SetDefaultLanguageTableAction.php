<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\Actions;

use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraLanguage\Actions\SetDefaultLanguageAction as SetDefaultLanguageDomainAction;
use Misaf\VendraLanguage\Filament\Clusters\Resources\Languages\LanguageResource;
use Misaf\VendraLanguage\Models\Language;

final class SetDefaultLanguageTableAction
{
    /** @param class-string<resource> $resource */
    public static function make(string $resource = LanguageResource::class): Action
    {
        return Action::make('setDefault')
            ->action(function (Action $action, Language $record, SetDefaultLanguageDomainAction $setDefaultLanguage): void {
                $setDefaultLanguage->execute($record);
                $action->success();
            })
            ->authorize(fn (Language $record): bool => $resource::canEdit($record))
            ->icon(Heroicon::OutlinedCheckCircle)
            ->label(__('vendra-language::actions.set_default'))
            ->requiresConfirmation()
            ->successNotificationTitle(__('vendra-language::messages.default_language_updated'))
            ->visible(fn (Language $record): bool => ! $record->is_default);
    }
}
