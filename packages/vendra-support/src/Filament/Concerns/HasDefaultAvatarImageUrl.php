<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Concerns;

trait HasDefaultAvatarImageUrl /** @phpstan-ignore trait.unused */
{
    protected static function defaultAvatarImageUrl(string $name): string
    {
        $avatarName = str($name)
            ->trim()
            ->explode(' ')
            ->map(static fn (string $segment): string => filled($segment) ? mb_substr($segment, 0, 1) : '')
            ->join(' ');

        return 'https://ui-avatars.com/api/?name='.urlencode($avatarName).'&color=FFFFFF';
    }
}
