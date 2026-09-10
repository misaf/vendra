<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Context;
use Misaf\VendraSupport\Context\RequestJobContext;

it('stores and reads structured request and job identifiers', function (): void {
    $context = new RequestJobContext(
        traceId: 'trace-1',
        actorId: 5,
        actorType: 'web',
        operation: 'testing',
        tenantId: 10,
        idempotencyKey: 'payment-attempt-50',
        metadata: [
            'reseller_id' => 20,
            'subscription_id' => 30,
            'payment_id' => 40,
        ],
    );

    $context->add();

    expect(RequestJobContext::current())->toEqual($context)
        ->and(Context::all())->toMatchArray([
            'trace_id' => 'trace-1',
            'actor_id' => 5,
            'actor_type' => 'web',
            'operation' => 'testing',
            'tenant_id' => 10,
            'reseller_id' => 20,
            'subscription_id' => 30,
            'payment_id' => 40,
        ])
        ->and(Context::allHidden())->toBe([
            'idempotency_key' => 'payment-attempt-50',
        ]);
});

it('omits absent identifiers and restores scoped visible and hidden values', function (): void {
    Context::add(['tenant_id' => 10, 'payment_id' => 40]);
    Context::addHidden('idempotency_key', 'original');

    $captured = (new RequestJobContext(
        idempotencyKey: 'scoped',
        metadata: [
            'reseller_id' => 20,
            'payment_id' => 41,
            'newsletter_id' => null,
        ],
    ))->scope(function (): array {
        return [
            RequestJobContext::current(),
            Context::all(),
            Context::allHidden(),
        ];
    });

    expect($captured[0]->tenantId)->toBe(10)
        ->and($captured[0]->idempotencyKey)->toBe('scoped')
        ->and($captured[0]->metadata)->toBe([
            'payment_id' => 41,
            'reseller_id' => 20,
        ])
        ->and($captured[1])->toBe([
            'tenant_id' => 10,
            'payment_id' => 41,
            'reseller_id' => 20,
        ])
        ->and($captured[2])->toBe(['idempotency_key' => 'scoped'])
        ->and(Context::all())->toBe([
            'tenant_id' => 10,
            'payment_id' => 40,
        ])
        ->and(Context::allHidden())->toBe(['idempotency_key' => 'original']);
});

it('reuses an existing trace identifier or creates a UUID', function (): void {
    $generated = RequestJobContext::resolveTraceId();

    Context::add(RequestJobContext::TRACE_ID, 'existing-trace');

    expect($generated)->toBeUuid()
        ->and(RequestJobContext::resolveTraceId())->toBe('existing-trace');
});

it('forgets the given visible keys', function (): void {
    Context::add(['tenant_id' => 10, 'reseller_id' => 20]);

    RequestJobContext::forget(RequestJobContext::TENANT_ID);

    expect(Context::has('tenant_id'))->toBeFalse()
        ->and(Context::get('reseller_id'))->toBe(20);
});
