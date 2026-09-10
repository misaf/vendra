<?php

declare(strict_types=1);

namespace Misaf\VendraFaq\Database\Seeders;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Misaf\VendraFaq\Database\Factories\FaqCategoryFactory;
use Misaf\VendraFaq\Database\Factories\FaqFactory;
use Misaf\VendraFaq\Models\FaqCategory;
use Misaf\VendraSupport\Tenancy\Database\Seeders\DemoContentSeeder as BaseDemoContentSeeder;

final class DemoContentSeeder extends BaseDemoContentSeeder
{
    protected function seedFactories(): void
    {
        $this->currentTenantOrNull();

        FaqCategoryFactory::new()
            ->active()
            ->count(4)
            ->create()
            ->each(fn (FaqCategory $faqCategory): mixed => FaqFactory::new()
                ->forCategory($faqCategory)
                ->active()
                ->count(3)
                ->create());
    }

    /**
     * @param  list<array<string, mixed>>  $records
     */
    protected function seedFixtures(array $records): void
    {
        $this->currentTenantOrNull();

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
     *     faqs: list<array{
     *         name: non-empty-array<string, string>,
     *         description: non-empty-array<string, string>,
     *         slug: non-empty-array<string, string>,
     *         active: bool
     *     }>
     * } $data
     */
    private function handleSeedFixtureRecord(array $data): void
    {
        $faqCategory = FaqCategory::query()->create([
            'name' => Arr::get($data, 'name'),
            'description' => Arr::get($data, 'description'),
            'slug' => Arr::get($data, 'slug'),
            'active' => Arr::get($data, 'active'),
        ]);

        foreach (Arr::get($data, 'faqs') as $faqRecord) {
            $this->handleFaqFixtureRecord($faqCategory, $faqRecord);
        }
    }

    /**
     * @param array{
     *     name: non-empty-array<string, string>,
     *     description: non-empty-array<string, string>,
     *     slug: non-empty-array<string, string>,
     *     active: bool
     * } $faqRecord
     */
    private function handleFaqFixtureRecord(FaqCategory $faqCategory, array $faqRecord): void
    {
        $faqCategory->faqs()->create([
            'name' => Arr::get($faqRecord, 'name'),
            'description' => Arr::get($faqRecord, 'description'),
            'slug' => Arr::get($faqRecord, 'slug'),
            'active' => Arr::get($faqRecord, 'active'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array{
     *     name: non-empty-array<string, string>,
     *     description: non-empty-array<string, string>,
     *     slug: non-empty-array<string, string>,
     *     active: bool,
     *     faqs: list<array{
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
         *     faqs: list<array{
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
                'faqs' => ['required', 'array', 'list'],
                'faqs.*' => ['required', 'array:name,description,slug,active'],
                'faqs.*.name' => ['required', 'array', 'min:1'],
                'faqs.*.name.*' => ['required', 'string'],
                'faqs.*.description' => ['required', 'array', 'min:1'],
                'faqs.*.description.*' => ['required', 'string'],
                'faqs.*.slug' => ['required', 'array', 'min:1'],
                'faqs.*.slug.*' => ['required', 'string'],
                'faqs.*.active' => ['required', 'boolean'],
            ],
        )->validate();

        return $validated;
    }
}
