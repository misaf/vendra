<?php

declare(strict_types=1);

use Misaf\VendraInquiry\Actions\SubmitInquiryAction;
use Misaf\VendraInquiry\Database\Factories\InquiryFactory;
use Misaf\VendraInquiry\Models\Inquiry;
use Misaf\VendraInquiry\States\Answered;
use Misaf\VendraInquiry\States\Closed;
use Misaf\VendraInquiry\States\Open;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('records an enquiry exactly as it was written', function (): void {
    $message = "  Two weddings in Mordad.\n\nDo you still have dates?  ";

    $inquiry = resolve(SubmitInquiryAction::class)->execute(
        name: 'Nasrin K.',
        email: 'nasrin@example.com',
        message: $message,
        phone: '+98 21 8877 0134',
        occasion: 'wedding',
        source: 'contact-form',
        locale: 'fa',
    );

    expect($inquiry->message)->toBe($message)
        ->and($inquiry->status)->toBeInstanceOf(Open::class)
        ->and($inquiry->occasion)->toBe('wedding')
        ->and($inquiry->locale)->toBe('fa')
        ->and($inquiry->answered_at)->toBeNull();
});

it('marks an enquiry answered and stamps when', function (): void {
    $inquiry = InquiryFactory::new()->createOne();

    $inquiry->markAnswered();

    expect($inquiry->status)->toBeInstanceOf(Answered::class)
        ->and($inquiry->answered_at)->not->toBeNull();
});

it('closes and reopens an enquiry', function (): void {
    $inquiry = InquiryFactory::new()->answered()->createOne();

    $inquiry->close();

    expect($inquiry->status)->toBeInstanceOf(Closed::class);

    $inquiry->reopen();

    expect($inquiry->status)->toBeInstanceOf(Open::class)
        ->and($inquiry->answered_at)->toBeNull();
});

it('counts only unanswered enquiries for the inbox badge', function (): void {
    InquiryFactory::new()->count(2)->create();
    InquiryFactory::new()->answered()->createOne();
    InquiryFactory::new()->closed()->createOne();

    expect(Inquiry::query()->unanswered()->count())->toBe(2);
});
