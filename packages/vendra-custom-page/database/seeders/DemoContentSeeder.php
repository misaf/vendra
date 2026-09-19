<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPage\Database\Seeders;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Misaf\VendraCustomPage\Database\Factories\CustomPageCategoryFactory;
use Misaf\VendraCustomPage\Database\Factories\CustomPageFactory;
use Misaf\VendraCustomPage\Models\CustomPage;
use Misaf\VendraCustomPage\Models\CustomPageCategory;
use Misaf\VendraSupport\Tenancy\Database\Seeders\DemoContentSeeder as BaseDemoContentSeeder;

final class DemoContentSeeder extends BaseDemoContentSeeder
{
    protected const array FACTORIES = [CustomPageCategoryFactory::class, CustomPageFactory::class];

    protected function seedFactories(): void
    {
        CustomPageCategoryFactory::new()
            ->active()
            ->count(2)
            ->create()
            ->each(fn (CustomPageCategory $customPageCategory): mixed => CustomPageFactory::new()
                ->forCategory($customPageCategory)
                ->active()
                ->count(2)
                ->create());
    }

    /**
     * Fixtures are keyed on the translated slug of the record's first locale,
     * so a repeated run of the same fixture file updates nothing and inserts
     * nothing. Store provisioning retries the whole seed list on failure, so a
     * partial run has to be safe to repeat.
     *
     * @param  list<array<string, mixed>>  $records
     */
    protected function seedFixtures(array $records): void
    {
        foreach ($records as $record) {
            $this->handleSeedFixtureRecord($this->validatedFixtureRecord($record));
        }
    }

    /**
     * @param array{
     *     name: non-empty-array<string, string>,
     *     description: non-empty-array<string, string>,
     *     slug: non-empty-array<string, string>,
     *     active: bool,
     *     custom_pages: list<array{
     *         name: non-empty-array<string, string>,
     *         description: non-empty-array<string, string>,
     *         slug: non-empty-array<string, string>,
     *         active: bool
     *     }>
     * } $data
     */
    private function handleSeedFixtureRecord(array $data): void
    {
        $slug = Arr::get($data, 'slug');
        $locale = array_key_first($slug);

        $customPageCategory = CustomPageCategory::query()
            ->where('slug->'.$locale, $slug[$locale])
            ->first()
            ?? CustomPageCategory::query()->create([
                'name' => Arr::get($data, 'name'),
                'description' => Arr::get($data, 'description'),
                'slug' => Arr::get($data, 'slug'),
                'active' => Arr::get($data, 'active'),
            ]);

        foreach (Arr::get($data, 'custom_pages') as $customPageRecord) {
            $this->handleCustomPageFixtureRecord($customPageCategory, $customPageRecord);
        }
    }

    /**
     * @param array{
     *     name: non-empty-array<string, string>,
     *     description: non-empty-array<string, string>,
     *     slug: non-empty-array<string, string>,
     *     active: bool
     * } $customPageRecord
     */
    private function handleCustomPageFixtureRecord(CustomPageCategory $customPageCategory, array $customPageRecord): void
    {
        $slug = Arr::get($customPageRecord, 'slug');
        $locale = array_key_first($slug);

        $existingCustomPage = $customPageCategory->customPages()
            ->where('slug->'.$locale, $slug[$locale])
            ->first();

        if ($existingCustomPage instanceof CustomPage) {
            return;
        }

        $customPageCategory->customPages()->create([
            'name' => Arr::get($customPageRecord, 'name'),
            'description' => Arr::get($customPageRecord, 'description'),
            'slug' => Arr::get($customPageRecord, 'slug'),
            'active' => Arr::get($customPageRecord, 'active'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array{
     *     name: non-empty-array<string, string>,
     *     description: non-empty-array<string, string>,
     *     slug: non-empty-array<string, string>,
     *     active: bool,
     *     custom_pages: list<array{
     *         name: non-empty-array<string, string>,
     *         description: non-empty-array<string, string>,
     *         slug: non-empty-array<string, string>,
     *         active: bool
     *     }>
     * }
     */
    private function validatedFixtureRecord(array $record): array
    {
        /** @var array{
         *     name: non-empty-array<string, string>,
         *     description: non-empty-array<string, string>,
         *     slug: non-empty-array<string, string>,
         *     active: bool,
         *     custom_pages: list<array{
         *         name: non-empty-array<string, string>,
         *         description: non-empty-array<string, string>,
         *         slug: non-empty-array<string, string>,
         *         active: bool
         *     }>
         * } $validated
         */
        $validated = Validator::make(
            data: $record,
            rules: [
                'name' => ['required', 'array', 'min:1'],
                'name.*' => ['required', 'string'],
                'description' => ['required', 'array', 'min:1'],
                'description.*' => ['required', 'string'],
                'slug' => ['required', 'array', 'min:1'],
                'slug.*' => ['required', 'string'],
                'active' => ['required', 'boolean'],
                'custom_pages' => ['required', 'array', 'list'],
                'custom_pages.*' => ['required', 'array:name,description,slug,active'],
                'custom_pages.*.name' => ['required', 'array', 'min:1'],
                'custom_pages.*.name.*' => ['required', 'string'],
                'custom_pages.*.description' => ['required', 'array', 'min:1'],
                'custom_pages.*.description.*' => ['required', 'string'],
                'custom_pages.*.slug' => ['required', 'array', 'min:1'],
                'custom_pages.*.slug.*' => ['required', 'string'],
                'custom_pages.*.active' => ['required', 'boolean'],
            ],
        )->validate();

        return $validated;
    }
}
