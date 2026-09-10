<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Database\Seeders;

use Illuminate\Database\Seeder;
use Misaf\VendraSupport\Tenancy\RequiresCurrentTenant;
use Misaf\VendraUser\Models\User;
use Misaf\VendraUserProfile\Models\UserProfile;

final class DatabaseSeeder extends Seeder
{
    use RequiresCurrentTenant;

    public function run(): void
    {
        $this->currentTenantOrNull();

        $this->seedUserProfiles();
    }

    private function seedUserProfiles(): void
    {
        $users = User::query()->get();

        $createdCount = 0;
        $existingCount = 0;

        foreach ($users as $user) {
            $userProfile = UserProfile::query()->firstOrCreate([
                'user_id' => $user->id,
                'name' => $user->username,
                'active' => true,
            ]);

            if ($userProfile->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $existingCount++;
            }
        }

        $this->command?->info(sprintf(
            'Successfully seeded %d user profiles. %d created, %d already existed.',
            $users->count(),
            $createdCount,
            $existingCount,
        ));
    }
}
