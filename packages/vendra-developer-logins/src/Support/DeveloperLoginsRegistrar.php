<?php

declare(strict_types=1);

namespace Misaf\VendraDeveloperLogins\Support;

use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Misaf\VendraPermission\Models\Role;

final class DeveloperLoginsRegistrar
{
    public static function make(): FilamentDeveloperLoginsPlugin
    {
        return FilamentDeveloperLoginsPlugin::make()
            ->enabled(fn (): bool => self::isEnabled() && self::hasUsers())
            ->switchable(Config::boolean('vendra-developer-logins.switchable', true))
            ->column(Config::string('vendra-developer-logins.column', 'email'))
            ->users(fn (): array => self::users())
            ->modelClass(DeveloperLoginsUsers::model());
    }

    /**
     * @return array<string, string>
     */
    public static function users(): array
    {
        $role = self::role();

        if ($role === null) {
            return [];
        }

        $credentialColumn = Config::string('vendra-developer-logins.column', 'email');
        $labelColumn = Config::string('vendra-developer-logins.label_column', 'username');

        $userModel = DeveloperLoginsUsers::model();

        return $userModel::query()
            ->role($role)
            ->get([$labelColumn, $credentialColumn])
            ->mapWithKeys(static function (Model $user) use ($credentialColumn, $labelColumn): array {
                $credential = $user->getAttribute($credentialColumn);
                $label = $user->getAttribute($labelColumn);

                if (! is_string($credential) || $credential === '' || ! is_string($label) || $label === '') {
                    return [];
                }

                return [$label => $credential];
            })
            ->all();
    }

    public static function hasUsers(): bool
    {
        return self::users() !== [];
    }

    private static function isEnabled(): bool
    {
        return App::environment('local')
            && Config::boolean('vendra-developer-logins.enabled', true);
    }

    private static function role(): ?Role
    {
        $configuredRole = Config::get('vendra-developer-logins.role');
        $roleName = is_string($configuredRole) && $configuredRole !== ''
            ? $configuredRole
            : Config::string('vendra-permission.admin_role');

        return Role::query()
            ->where('name', $roleName)
            ->where('guard_name', Config::string('auth.defaults.guard'))
            ->first();
    }
}
