<?php

declare(strict_types=1);

namespace Misaf\VendraCart\Database\Seeders;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Misaf\VendraCart\Database\Factories\CartFactory;
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraSupport\Tenancy\Database\Seeders\DemoContentSeeder as BaseDemoContentSeeder;

final class DemoContentSeeder extends BaseDemoContentSeeder
{
    protected function seedFactories(): void
    {
        $this->currentTenantOrNull();

        $owners = $this->owners();

        if ($owners->isEmpty()) {
            CartFactory::new()
                ->count(5)
                ->create();

            return;
        }

        for ($index = 0; $index < 5; $index++) {
            $owner = $owners->get($index % $owners->count());

            if ($owner instanceof Model) {
                CartFactory::new()
                    ->forOwner($owner)
                    ->create();
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $records
     */
    protected function seedFixtures(array $records): void
    {
        $this->currentTenantOrNull();

        $owners = $this->owners();

        foreach ($records as $index => $record) {
            $cart = new Cart($this->validatedFixtureRecord($record));
            $owner = $owners->isEmpty()
                ? null
                : $owners->get($index % $owners->count());

            if ($owner instanceof Model) {
                $cart->owner()->associate($owner);
            }

            $cart->save();
        }
    }

    /**
     * @return Collection<int, Model>
     */
    private function owners(): Collection
    {
        $ownerModel = Config::string('auth.providers.users.model');

        if (! is_subclass_of($ownerModel, Model::class)) {
            return new Collection;
        }

        return $ownerModel::query()
            ->limit(5)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array{token: string, expires_at: string|null}
     */
    private function validatedFixtureRecord(array $record): array
    {
        /** @var array{token: string, expires_at: string|null} $validated */
        $validated = Validator::make(
            data: $record,
            rules: [
                'token' => ['required', 'uuid'],
                'expires_at' => ['nullable', 'date'],
            ],
        )->validate();

        return $validated;
    }
}
