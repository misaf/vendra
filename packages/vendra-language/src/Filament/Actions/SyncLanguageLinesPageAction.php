<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Filament\Actions;

use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraLanguage\Actions\SyncLanguageLinesAction;
use Misaf\VendraLanguage\Filament\Clusters\Resources\LanguageLines\LanguageLineResource;
use Misaf\VendraLanguage\Models\LanguageLine;

final class SyncLanguageLinesPageAction
{
    /** @param class-string<resource> $resource */
    public static function make(string $resource = LanguageLineResource::class): Action
    {
        return Action::make('syncLanguageLines')
            ->action(function (Action $action, SyncLanguageLinesAction $syncLanguageLines): void {
                $result = $syncLanguageLines->execute();

                $action
                    ->successNotificationTitle(__('vendra-language::messages.language_lines_synchronized', $result))
                    ->success();
            })
            ->authorize(fn (): bool => $resource::canCreate() && $resource::canEdit(new LanguageLine))
            ->icon(Heroicon::OutlinedArrowPath)
            ->label(__('vendra-language::actions.sync_language_lines'))
            ->modalDescription(__('vendra-language::messages.sync_language_lines_description'))
            ->requiresConfirmation();
    }
}
