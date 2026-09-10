<?php

declare(strict_types=1);

use Illuminate\Validation\ValidationException;
use Misaf\VendraInquiry\Actions\SubmitInquiryAction;
use Misaf\VendraInquiry\Database\Factories\InquiryFactory;
use Misaf\VendraInquiry\Enums\InquiryStatusEnum;
use Misaf\VendraInquiry\Models\Inquiry;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('records an enquiry exactly as it was written', function (): void {
    $message = "  Two weddings in Mordad.\n\nDo you still have dates?  ";

    $inquiry = app(SubmitInquiryAction::class)->execute(
        name: 'Nasrin K.',
        email: 'nasrin@example.com',
        message: $message,
        phone: '+98 21 8877 0134',
        occasion: 'wedding',
        source: 'contact-form',
        locale: 'fa',
    );

    expect($inquiry->message)->toBe($message)
        ->and($inquiry->status)->toBe(InquiryStatusEnum::New)
        ->and($inquiry->occasion)->toBe('wedding')
        ->and($inquiry->locale)->toBe('fa')
        ->and($inquiry->answered_at)->toBeNull();
});

it('refuses an enquiry without a usable email or message', function (array $overrides): void {
    expect(fn (): Inquiry => app(SubmitInquiryAction::class)->execute(
        name: $overrides['name'] ?? 'Nasrin K.',
        email: $overrides['email'] ?? 'nasrin@example.com',
        message: $overrides['message'] ?? 'Do you deliver on Fridays?',
    ))->toThrow(ValidationException::class);

    expect(Inquiry::query()->count())->toBe(0);
})->with([
    'missing name' => [['name' => '']],
    'invalid email' => [['email' => 'not-an-email']],
    'empty message' => [['message' => '']],
]);

it('marks an enquiry answered and stamps when', function (): void {
    $inquiry = InquiryFactory::new()->createOne();

    $inquiry->markAnswered();

    expect($inquiry->status)->toBe(InquiryStatusEnum::Answered)
        ->and($inquiry->answered_at)->not->toBeNull();
});

it('closes and reopens an enquiry', function (): void {
    $inquiry = InquiryFactory::new()->answered()->createOne();

    $inquiry->close();

    expect($inquiry->status)->toBe(InquiryStatusEnum::Closed);

    $inquiry->reopen();

    expect($inquiry->status)->toBe(InquiryStatusEnum::New)
        ->and($inquiry->answered_at)->toBeNull();
});

it('counts only unanswered enquiries for the inbox badge', function (): void {
    InquiryFactory::new()->count(2)->create();
    InquiryFactory::new()->answered()->createOne();
    InquiryFactory::new()->closed()->createOne();

    expect(Inquiry::query()->unanswered()->count())->toBe(2);
});
