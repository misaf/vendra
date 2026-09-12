<?php

declare(strict_types=1);

use Misaf\VendraInquiry\Actions\AnswerInquiryAction;
use Misaf\VendraInquiry\Actions\CloseInquiryAction;
use Misaf\VendraInquiry\Actions\ReopenInquiryAction;
use Misaf\VendraInquiry\Database\Factories\InquiryFactory;
use Misaf\VendraInquiry\Enums\InquiryStatusEnum;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('marks an enquiry answered and stamps when through the domain action', function (): void {
    $inquiry = InquiryFactory::new()->createOne();

    resolve(AnswerInquiryAction::class)->execute($inquiry);

    expect($inquiry->fresh()?->status)->toBe(InquiryStatusEnum::Answered)
        ->and($inquiry->fresh()?->answered_at)->not->toBeNull();
});

it('closes an enquiry through the domain action', function (): void {
    $inquiry = InquiryFactory::new()->answered()->createOne();

    resolve(CloseInquiryAction::class)->execute($inquiry);

    expect($inquiry->fresh()?->status)->toBe(InquiryStatusEnum::Closed);
});

it('reopens an enquiry and clears the answered stamp through the domain action', function (): void {
    $inquiry = InquiryFactory::new()->closed()->createOne();

    resolve(ReopenInquiryAction::class)->execute($inquiry);

    expect($inquiry->fresh()?->status)->toBe(InquiryStatusEnum::New)
        ->and($inquiry->fresh()?->answered_at)->toBeNull();
});
