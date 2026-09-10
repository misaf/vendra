<?php

declare(strict_types=1);

use Misaf\VendraNewsletter\Enums\NewsletterStatusEnum;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Pages\CreateNewsletter;
use Misaf\VendraNewsletter\Models\Newsletter;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();
});

it('shows the schedule field when the scheduled status is selected', function (): void {
    $component = livewire(CreateNewsletter::class)
        ->assertFormFieldHidden('scheduled_at')
        ->fillForm([
            'status' => NewsletterStatusEnum::Scheduled->value,
        ]);

    expect($component->get('data.status'))->toBe(NewsletterStatusEnum::Scheduled->value);

    $component->assertFormFieldVisible('scheduled_at');
});

it('creates a scheduled newsletter from the form', function (): void {
    $scheduledAt = now()->addDay()->startOfMinute();

    livewire(CreateNewsletter::class)
        ->fillForm([
            'subject' => 'Scheduled newsletter',
            'status' => NewsletterStatusEnum::Scheduled->value,
            'scheduled_at' => $scheduledAt,
            'content' => 'Newsletter content',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $newsletter = Newsletter::query()
        ->where('subject', 'Scheduled newsletter')
        ->firstOrFail();

    expect($newsletter->status)->toBe(NewsletterStatusEnum::Scheduled)
        ->and($newsletter->scheduled_at?->equalTo($scheduledAt))->toBeTrue();
});
