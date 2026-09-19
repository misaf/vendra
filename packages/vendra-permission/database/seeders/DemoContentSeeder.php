<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Misaf\VendraPermission\Actions\CreateRoleAction;
use Misaf\VendraPermission\Database\Factories\RoleFactory;
use Misaf\VendraPermission\Models\Role;
use Misaf\VendraSupport\Tenancy\Database\Seeders\DemoContentSeeder as BaseDemoContentSeeder;

final class DemoContentSeeder extends BaseDemoContentSeeder
{
    protected const array FACTORIES = [RoleFactory::class];

    public function __construct(private readonly CreateRoleAction $createRoleAction) {}

    protected function seedFactories(): void
    {
        RoleFactory::new()->createOne();
    }

    /**
     * Name and guard name are the natural key — together they carry a
     * tenant-scoped unique index — so an already seeded role is left alone
     * rather than created a second time. The seed command makes the tenant
     * current for the run, so the lookup is scoped to it. Store provisioning
     * retries the whole seed list on failure, so a partial run has to be safe
     * to repeat.
     *
     * @param  list<array<string, mixed>>  $records
     */
    protected function seedFixtures(array $records): void
    {
        $tenant = $this->currentTenantOrNull();

        foreach ($records as $record) {
            $this->seedFixtureRecord($tenant, $record);
        }
    }

    /**
     * @param  array<string, mixed>  $record
     */
    protected function seedFixtureRecord(?Model $tenant, array $record): void
    {
        $data = $this->validatedFixtureRecord($record);

        $this->handleSeedFixtureRecord($tenant, $data);
    }

    /**
     * @param array{
     *     name: string,
     *     description?: string|null,
     *     guard_name: string
     * } $data
     */
    private function handleSeedFixtureRecord(?Model $tenant, array $data): void
    {
        $roleExists = Role::query()
            ->where('name', Arr::get($data, 'name'))
            ->where('guard_name', Arr::get($data, 'guard_name'))
            ->exists();

        if ($roleExists) {
            return;
        }

        $this->createRoleAction->execute(
            tenant: $tenant,
            name: Arr::get($data, 'name'),
            description: Arr::get($data, 'description', null),
            guardName: Arr::get($data, 'guard_name'),
        );
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array{
     *     name: string,
     *     description?: string|null,
     *     guard_name: string
     * }
     */
    private function validatedFixtureRecord(array $record): array
    {
        /** @var array{
         *     name: string,
         *     description?: string|null,
         *     guard_name: string
         * } $validated
         */
        $validated = Validator::make(
            data: $record,
            rules: [
                'name' => ['required', 'string'],
                'description' => ['nullable', 'string'],
                'guard_name' => ['required', 'string'],
            ],
        )->validate();

        return $validated;
    }
}
