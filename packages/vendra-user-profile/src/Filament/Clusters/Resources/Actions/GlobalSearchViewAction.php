<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Filament\Clusters\Resources\Actions;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\UserProfileResource;

final class GlobalSearchViewAction
{
    public static function make(Model $record): Action
    {
        return Action::make('view')
            ->url(UserProfileResource::getUrl('view', ['record' => $record]));
    }
}
