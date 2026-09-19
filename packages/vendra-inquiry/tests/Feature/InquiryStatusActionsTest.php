<?php

declare(strict_types=1);

use Misaf\VendraInquiry\Actions\AnswerInquiryAction;
use Misaf\VendraInquiry\Actions\CloseInquiryAction;
use Misaf\VendraInquiry\Actions\ReopenInquiryAction;
use Misaf\VendraInquiry\Database\Factories\InquiryFactory;
use Misaf\VendraInquiry\States\Answered;
use Misaf\VendraInquiry\States\Closed;
use Misaf\VendraInquiry\States\Open;
use Spatie\ModelStates\Exceptions\TransitionNotFound;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('marks an enquiry answered and stamps when through the domain action', function (): void {
    $inquiry = InquiryFactory::new()->createOne();

    resolve(AnswerInquiryAction::class)->execute($inquiry);

    expect($inquiry->fresh()?->status)->toBeInstanceOf(Answered::class)
        ->and($inquiry->fresh()?->answered_at)->not->toBeNull();
});

it('closes an enquiry through the domain action', function (): void {
    $inquiry = InquiryFactory::new()->answered()->createOne();

    resolve(CloseInquiryAction::class)->execute($inquiry);

    expect($inquiry->fresh()?->status)->toBeInstanceOf(Closed::class);
});

it('reopens an enquiry and clears the answered stamp through the domain action', function (): void {
    $inquiry = InquiryFactory::new()->closed()->createOne();

    resolve(ReopenInquiryAction::class)->execute($inquiry);

    expect($inquiry->fresh()?->status)->toBeInstanceOf(Open::class)
        ->and($inquiry->fresh()?->answered_at)->toBeNull();
});

it('refuses to move an enquiry to the status it already has', function (?string $state, string $action): void {
    $factory = InquiryFactory::new();
    $inquiry = ($state === null ? $factory : $factory->{$state}())->createOne();
    $answeredAt = $inquiry->answered_at;

    expect(fn (): mixed => resolve($action)->execute($inquiry))->toThrow(TransitionNotFound::class)
        ->and($inquiry->fresh()?->answered_at?->toIso8601String())->toBe($answeredAt?->toIso8601String());
})->with([
    'answer an answered enquiry' => ['answered', AnswerInquiryAction::class],
    'close a closed enquiry' => ['closed', CloseInquiryAction::class],
    'reopen an open enquiry' => [null, ReopenInquiryAction::class],
]);
