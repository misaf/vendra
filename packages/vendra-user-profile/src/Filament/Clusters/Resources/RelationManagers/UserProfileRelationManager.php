<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Filament\Clusters\Resources\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use Misaf\VendraUser\Models\User;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\UserProfileResource;
use Misaf\VendraUserProfile\Models\UserProfile;

final class UserProfileRelationManager extends RelationManager
{
    protected static string $relationship = 'userProfiles';

    protected static bool $isBadgeDeferred = true;

    public static function getModelLabel(): string
    {
        return __('vendra-user-profile::navigation.user_profiles');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-user-profile::navigation.user_profiles');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-user-profile::navigation.user_profile');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): string
    {
        if (! $ownerRecord instanceof User) {
            return (string) Number::format(0);
        }

        return (string) Number::format(
            UserProfile::query()
                ->where('user_id', $ownerRecord->getKey())
                ->count(),
        );
    }

    public function form(Schema $schema): Schema
    {
        return UserProfileResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return UserProfileResource::table($table)
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
