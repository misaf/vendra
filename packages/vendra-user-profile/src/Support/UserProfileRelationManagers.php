<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Support;

use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\RelationManagers\RelationManagerConfiguration;
use Illuminate\Support\Arr;

final class UserProfileRelationManagers
{
    /**
     * @var list<array{
     *     relationManager: class-string<RelationManager>|RelationGroup|RelationManagerConfiguration,
     *     priority: int
     * }>
     */
    private array $relationManagers = [];

    /**
     * @param  class-string<RelationManager>|RelationGroup|RelationManagerConfiguration  $relationManager
     */
    public function register(string|RelationGroup|RelationManagerConfiguration $relationManager, int $priority = 100): void
    {
        $this->relationManagers[] = [
            'relationManager' => $relationManager,
            'priority' => $priority,
        ];
    }

    /** @return array<class-string<RelationManager>|RelationGroup|RelationManagerConfiguration> */
    public function all(): array
    {
        usort(
            $this->relationManagers,
            fn (array $left, array $right): int => Arr::get($left, 'priority') <=> Arr::get($right, 'priority'),
        );

        return array_column($this->relationManagers, 'relationManager');
    }
}
