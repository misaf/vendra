<?php

declare(strict_types=1);

namespace Misaf\VendraSocialite\Support;

use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Misaf\VendraSocialite\Models\SocialiteUser;
use Misaf\VendraUser\Models\User;

/**
 * Builds the tenant-aware filament-socialite plugin: Google and GitHub OAuth
 * providers wired to the Vendra `User` and this module's `SocialiteUser`, with
 * account creation mapped onto the `username`-based user schema.
 */
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
     * Create a Vendra user from an OAuth identity, mapping the provider profile
     * onto the `username`-based schema and treating the OAuth email as verified.
     * Tenant assignment is handled by `BelongsToTenant`.
     */
    public static function createUserUsing(string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin): User
    {
        return User::query()->create([
            'username' => self::generateUsername($oauthUser->getNickname() ?? $oauthUser->getName() ?? $oauthUser->getEmail()),
            'email' => $oauthUser->getEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make(Str::password(32)),
        ]);
    }

    /**
     * Derive a unique, schema-valid username (letters, digits, dashes and
     * underscores) from an OAuth profile value.
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
