<?php

declare(strict_types=1);

use Misaf\VendraSubscription\Actions\IssueSubscriptionInvoiceAction;
use Misaf\VendraSubscription\Models\SubscriptionInvoice;
use Misaf\VendraSubscription\Models\SubscriptionPayment;
use Misaf\VendraSubscription\Support\SubscriptionInvoicePdf;

it('renders an invoice as a PDF in both text directions', function (string $locale): void {
    $invoice = SubscriptionInvoice::factory()->create();

    expect(SubscriptionInvoicePdf::render($invoice, $locale))->toStartWith('%PDF');
})->with(['en', 'fa']);

it('refuses to invoice a payment that has not been paid', function (): void {
    resolve(IssueSubscriptionInvoiceAction::class)->execute(SubscriptionPayment::factory()->create());
})->throws(LogicException::class);

it('shows the tax rate as a plain percentage', function (int $rate, string $percentage): void {
    expect(SubscriptionInvoice::factory()->make(['tax_rate' => $rate])->taxPercentage())->toBe($percentage);
})->with([
    [1_900, '19'],
    [750, '7.5'],
    [0, '0'],
]);
