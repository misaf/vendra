<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Database\Seeders;

use Illuminate\Support\Facades\Validator;
use Misaf\VendraNewsletter\Database\Factories\NewsletterFactory;
use Misaf\VendraNewsletter\Database\Factories\NewsletterSubscriberFactory;
use Misaf\VendraNewsletter\Enums\NewsletterStatusEnum;
use Misaf\VendraNewsletter\Models\Newsletter;
use Misaf\VendraNewsletter\Models\NewsletterSubscriber;
use Misaf\VendraSupport\Tenancy\Database\Seeders\DemoContentSeeder as BaseDemoContentSeeder;
use Misaf\VendraSupport\Tenancy\RequiresCurrentTenant;

final class DemoContentSeeder extends BaseDemoContentSeeder
{
    use RequiresCurrentTenant;

    protected function seedFactories(): void
    {
        $this->currentTenantOrNull();

        NewsletterFactory::new()->draft()->count(3)->create();
        NewsletterFactory::new()->scheduled()->count(2)->create();
        NewsletterFactory::new()->sent()->count(3)->create();

        NewsletterSubscriberFactory::new()->subscribed()->count(20)->create();
        NewsletterSubscriberFactory::new()->unsubscribed()->count(5)->create();
    }

    /**
     * @param  list<array<string, mixed>>  $records
     */
    protected function seedFixtures(array $records): void
    {
        $this->currentTenantOrNull();

        foreach ($records as $record) {
            $this->seedFixtureRecord($record);
        }

        $this->seedSubscriberFixtures();
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function seedFixtureRecord(array $record): void
    {
        $data = $this->validatedFixtureRecord($record);

        Newsletter::create([
            'subject' => $data['subject'],
            'content' => $data['content'],
            'status' => NewsletterStatusEnum::from($data['status']),
            'scheduled_at' => $data['scheduled_at'],
            'sent_at' => $data['sent_at'],
        ]);
    }

    private function seedSubscriberFixtures(): void
    {
        $subscribers = [
            ['email' => 'alex.rivera@example.com', 'name' => 'Alex Rivera'],
            ['email' => 'sam.chen@example.com', 'name' => 'Sam Chen'],
            ['email' => 'jordan.blake@example.com', 'name' => 'Jordan Blake'],
            ['email' => 'taylor.moss@example.com', 'name' => 'Taylor Moss'],
            ['email' => 'morgan.reid@example.com', 'name' => 'Morgan Reid'],
        ];

        foreach ($subscribers as $subscriber) {
            NewsletterSubscriber::create($subscriber);
        }
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array{
     *     subject: string,
     *     content: string,
     *     status: string,
     *     scheduled_at: string|null,
     *     sent_at: string|null
     * }
     */
    private function validatedFixtureRecord(array $record): array
    {
        /** @var array{
         *     subject: string,
         *     content: string,
         *     status: string,
         *     scheduled_at: string|null,
         *     sent_at: string|null
         * } $validated
         */
        $validated = Validator::make(
            data: $record,
            rules: [
                'subject' => ['required', 'string'],
                'content' => ['required', 'string'],
                'status' => ['required', 'string', 'in:draft,scheduled,sent'],
                'scheduled_at' => ['nullable', 'string'],
                'sent_at' => ['nullable', 'string'],
            ],
        )->validate();

        return $validated;
    }
}
