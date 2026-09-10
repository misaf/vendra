<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Filament\Clusters\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Clusters\Cluster;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\RelationManagers\RelationManagerConfiguration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Laravel\Pennant\Feature;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Filament\Clusters\CustomersCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;
use Misaf\VendraUserProfile\Features\ModuleEnabled;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\Actions\GlobalSearchViewAction;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\Pages\CreateUserProfile;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\Pages\EditUserProfile;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\Pages\ListUserProfiles;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\Pages\ViewUserProfile;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\Schemas\UserProfileForm;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\Schemas\UserProfileInfolist;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\Tables\UserProfileTable;
use Misaf\VendraUserProfile\Models\UserProfile;
use Misaf\VendraUserProfile\Support\UserProfileRelationManagers;

final class UserProfileResource extends Resource
{
    protected static ?string $model = UserProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?int $navigationSort = NavigationPriority::UserProfiles->value;

    protected static ?string $slug = 'user-profiles';

    /**
     * @var class-string<Cluster>|null
     */
    protected static ?string $cluster = CustomersCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('vendra-user-profile::navigation.user_profile');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-user-profile::navigation.user_profile');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-user-profile::navigation.user_profiles');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-user-profile::navigation.user_profiles');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'user.username', 'user.email'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('user');
    }

    /**
     * @return array<Action>
     */
    public static function getGlobalSearchResultActions(Model $record): array
    {
        return [
            GlobalSearchViewAction::make($record),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $profile = self::profile($record);

        return [
            __('vendra-user-profile::attributes.email') => (string) $profile->user?->email,
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return self::profile($record)->name;
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListUserProfiles::route('/'),
            'create' => CreateUserProfile::route('/create'),
            'view' => ViewUserProfile::route('/{record}'),
            'edit' => EditUserProfile::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<class-string<RelationManager>|RelationGroup|RelationManagerConfiguration>
     */
    public static function getRelations(): array
    {
        return resolve(UserProfileRelationManagers::class)->all();
    }

    public static function form(Schema $schema): Schema
    {
        return UserProfileForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserProfileInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserProfileTable::configure($table);
    }

    public static function canAccess(): bool
    {
        $tenant = resolve(TenantResolver::class)->current();

        return Feature::for($tenant)->active(ModuleEnabled::class);
    }

    private static function profile(Model $record): UserProfile
    {
        throw_unless($record instanceof UserProfile, InvalidArgumentException::class, 'User Profile resources require a UserProfile record.');

        return $record;
    }
}
