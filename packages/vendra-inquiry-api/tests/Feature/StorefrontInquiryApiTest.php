<?php

declare(strict_types=1);

use Misaf\VendraInquiry\Enums\InquiryStatusEnum;
use Misaf\VendraInquiry\Models\Inquiry;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('accepts a contact enquiry from an unauthenticated visitor', function (): void {
    $message = "Two weddings in Mordad.\n\nDo you still have dates?";

    $this->postJson('/api/support/inquiries', [
        'name' => 'Nasrin K.',
        'email' => 'nasrin@example.com',
        'message' => $message,
        'phone' => '+98 21 8877 0134',
        'occasion' => 'wedding',
        'preferredLocale' => 'fa',
    ])->assertNoContent();

    $inquiry = Inquiry::query()->firstOrFail();

    expect($inquiry->name)->toBe('Nasrin K.')
        ->and($inquiry->email)->toBe('nasrin@example.com')
        ->and($inquiry->message)->toBe($message)
        ->and($inquiry->occasion)->toBe('wedding')
        ->and($inquiry->locale)->toBe('fa')
        ->and($inquiry->source)->toBe('storefront')
        ->and($inquiry->status)->toBe(InquiryStatusEnum::New);
});

it('rejects an enquiry the studio could not answer', function (array $payload): void {
    $this->postJson('/api/support/inquiries', $payload)->assertUnprocessable();

    expect(Inquiry::query()->count())->toBe(0);
})->with([
    'no name' => [['email' => 'nasrin@example.com', 'message' => 'Do you deliver on Fridays?']],
    'bad email' => [['name' => 'Nasrin K.', 'email' => 'not-an-email', 'message' => 'Do you deliver on Fridays?']],
    'no message' => [['name' => 'Nasrin K.', 'email' => 'nasrin@example.com']],
    'empty message' => [['name' => 'Nasrin K.', 'email' => 'nasrin@example.com', 'message' => '']],
]);

it('does not take the source from the payload', function (): void {
    $this->postJson('/api/support/inquiries', [
        'name' => 'Nasrin K.',
        'email' => 'nasrin@example.com',
        'message' => 'Do you deliver on Fridays?',
        'source' => 'spoofed',
    ])->assertNoContent();

    expect(Inquiry::query()->firstOrFail()->source)->toBe('storefront');
});

it('exposes no way to read enquiries back', function (): void {
    $this->postJson('/api/support/inquiries', [
        'name' => 'Nasrin K.',
        'email' => 'nasrin@example.com',
        'message' => 'Do you deliver on Fridays?',
    ])->assertNoContent();

    $this->getJson('/api/support/inquiries', ['Accept' => 'application/ld+json'])
        ->assertStatus(405);
});
