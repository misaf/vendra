<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Misaf\VendraPermission\Actions\CreateRoleAction;
use Misaf\VendraPermission\Database\Factories\RoleFactory;
use Misaf\VendraSupport\Tenancy\Database\Seeders\DemoContentSeeder as BaseDemoContentSeeder;
use Misaf\VendraSupport\Tenancy\RequiresCurrentTenant;

final class DemoContentSeeder extends BaseDemoContentSeeder
{
    use RequiresCurrentTenant;

    public function __construct(private readonly CreateRoleAction $createRoleAction) {}

    protected function seedFactories(): void
    {
        $this->currentTenantOrNull();

        RoleFactory::new()->createOne();
    }

    /**
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
