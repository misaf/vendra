<?php

declare(strict_types=1);

namespace Misaf\VendraSocialite\Support;

use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Misaf\VendraSocialite\Models\SocialiteUser;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraUser\Actions\CreateUserAction;
use Misaf\VendraUser\Models\User;
use Misaf\VendraUser\Support\PasswordGenerator;

final class SocialiteRegistrar
{
    public static function make(): FilamentSocialitePlugin
    {
        return FilamentSocialitePlugin::make()
            ->providers(self::providers())
            ->registration(Config::boolean('vendra-socialite.registration', false))
            ->domainAllowList(self::domainAllowList())
            ->userModelClass(User::class)
            ->socialiteUserModelClass(SocialiteUser::class)
            ->createUserUsing(self::createUserUsing(...));
    }

    /**
     * @return list<Provider>
     */
    private static function providers(): array
    {
        return [
            Provider::make('google')
                ->label('Google')
                ->icon('fab-google')
                ->color(Color::hex('#ea4335'))
                ->scopes(['openid', 'profile', 'email'])
                ->visible(fn (): bool => filled(Config::get('services.google.client_id'))),

            Provider::make('github')
                ->label('GitHub')
                ->icon('fab-github')
                ->color(Color::hex('#181717'))
                ->scopes(['read:user', 'user:email'])
                ->visible(fn (): bool => filled(Config::get('services.github.client_id'))),
        ];
    }

    /**
     * @return list<string>
     */
    private static function domainAllowList(): array
    {
        $raw = Config::get('vendra-socialite.domain_allow_list', []);

        if (is_string($raw)) {
            $raw = explode(',', $raw);
        }

        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn ($domain): string => is_string($domain) ? mb_strtolower(mb_trim($domain)) : '',
            $raw,
        )));
    }

    /**
     * Create a user in the current tenant from an OAuth identity, with a verified email.
     */
    public static function createUserUsing(string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin): User
    {
        $email = $oauthUser->getEmail() ?? throw new InvalidArgumentException("The [{$provider}] identity has no email address.");

        return resolve(CreateUserAction::class)->execute(
            tenant: resolve(TenantResolver::class)->current(),
            username: self::generateUsername($oauthUser->getNickname() ?? $oauthUser->getName() ?? $email),
            email: $email,
            password: PasswordGenerator::generate(),
        );
    }

    /**
     * Derive a unique, valid username from an OAuth profile value.
     */
    public static function generateUsername(?string $seed): string
    {
        $base = Str::of($seed ?? '')
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->toString();

        if (mb_strlen($base) < 3) {
            $base = 'user';
        }

        $base = mb_substr($base, 0, 12);
        $candidate = $base;

        while (User::query()->where('username', $candidate)->exists()) {
            $suffix = (string) random_int(10, 9999);
            $candidate = mb_substr($base, 0, max(1, 12 - mb_strlen($suffix))).$suffix;
        }

        return $candidate;
    }
}
