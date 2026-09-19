<?php

declare(strict_types=1);

namespace App\Settings\SettingsRepositories;

final class GlobalSettingsRepository extends ScopedSettingsRepository
{
    protected function tenantId(): ?int
    {
        return null;
    }
}
