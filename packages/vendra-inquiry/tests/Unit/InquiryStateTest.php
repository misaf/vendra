<?php

declare(strict_types=1);

use Misaf\VendraInquiry\Models\Inquiry;
use Misaf\VendraInquiry\States\Answered;
use Misaf\VendraInquiry\States\Closed;
use Misaf\VendraInquiry\States\InquiryState;
use Misaf\VendraInquiry\States\Open;

it('keeps the stored status values', function (): void {
    expect(InquiryState::all()->keys()->all())->toEqualCanonicalizing(['new', 'answered', 'closed']);
});

it('gives every status a colour and an icon', function (string $state): void {
    $status = new $state(new Inquiry);

    expect($status->getColor())->toBeArray()->not->toBeEmpty()
        ->and($status->getIcon())->not->toBeNull();
})->with([Open::class, Answered::class, Closed::class]);
