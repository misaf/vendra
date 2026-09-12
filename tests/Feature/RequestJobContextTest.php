<?php

declare(strict_types=1);

use App\Http\Middleware\AddRequestContext;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Misaf\VendraReseller\Http\Middleware\AddResellerToRequestJobContext;
use Misaf\VendraReseller\Models\Reseller;
use Misaf\VendraSupport\Context\ContextKeys;
use Misaf\VendraSupport\Context\RequestJobContext;
use Misaf\VendraSupport\Http\Middleware\AddPanelToRequestJobContext;
use Misaf\VendraUser\Models\User;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;

it('adds the authenticated reseller to the request and job context', function (): void {
    $reseller = Reseller::factory()->create();
    $user = User::factory()->create(['tenant_id' => null]);
    $reseller->users()->attach($user->getKey());
    actingAs($user, 'reseller');

    resolve(AddResellerToRequestJobContext::class)->handle(
        Request::create('https://reseller.vendra.test'),
        function (Request $request) use ($reseller): Response {
            expect(RequestJobContext::current()->metadata[ContextKeys::RESELLER_ID])->toBe($reseller->getKey());

            return new Response;
        },
    );
});

it('uses a validated request identifier for context and the response', function (): void {
    $requestId = 'D9428888-122B-11E1-B85C-61CD3CBB3210';
    $request = Request::create('https://vendra.test');
    $request->headers->set(AddRequestContext::REQUEST_ID_HEADER, $requestId);

    $response = resolve(AddRequestContext::class)->handle(
        $request,
        function (Request $request): Response {
            expect(RequestJobContext::current()->traceId)
                ->toBe('d9428888-122b-11e1-b85c-61cd3cbb3210');

            return new Response;
        },
    );

    expect($response->headers->get(AddRequestContext::REQUEST_ID_HEADER))
        ->toBe('d9428888-122b-11e1-b85c-61cd3cbb3210');
});

it('rejects an invalid incoming request identifier', function (): void {
    $request = Request::create('https://vendra.test');
    $request->headers->set(AddRequestContext::REQUEST_ID_HEADER, "untrusted\nvalue");

    $response = resolve(AddRequestContext::class)->handle(
        $request,
        fn (Request $request): Response => new Response,
    );

    expect(RequestJobContext::current()->traceId)->toBeUuid()
        ->and($response->headers->get(AddRequestContext::REQUEST_ID_HEADER))
        ->toBe(RequestJobContext::current()->traceId);
});

it('adds the current panel id without storing personal data', function (): void {
    Filament::setCurrentPanel('reseller');

    resolve(AddPanelToRequestJobContext::class)->handle(
        Request::create('https://reseller.vendra.test'),
        function (Request $request): Response {
            expect(RequestJobContext::current()->metadata[ContextKeys::PANEL_ID])->toBe('reseller')
                ->and(Context::all())
                ->not->toHaveKeys(['email', 'username']);

            return new Response;
        },
    );
});

it('adds an authenticated non-panel actor to request context', function (): void {
    $actor = User::factory()->create(['tenant_id' => null]);
    Context::flush();

    Event::dispatch(new Authenticated('sanctum', $actor));

    expect(RequestJobContext::current())
        ->actorId->toBe($actor->getKey())
        ->actorType->toBe('sanctum')
        ->and(Context::all())
        ->not->toHaveKeys(['email', 'username']);
});

it('logs an authentication failure with scoped operation context and no credentials', function (): void {
    $captured = null;

    Log::shouldReceive('warning')
        ->once()
        ->andReturnUsing(function (string $message) use (&$captured): void {
            $context = RequestJobContext::current();
            $captured = [$message, $context->operation, $context->actorType];
        });

    Event::dispatch(new Failed('web', null, ['email' => 'attacker@example.com']));

    expect($captured)->toBe(['Authentication attempt failed.', 'auth_failed', 'web'])
        ->and(RequestJobContext::current()->operation)->toBeNull()
        ->and(Context::all())->not->toHaveKey('email');
});

it('logs an authentication lockout with scoped operation context', function (): void {
    $captured = null;

    Log::shouldReceive('warning')
        ->once()
        ->andReturnUsing(function (string $message) use (&$captured): void {
            $captured = [$message, RequestJobContext::current()->operation];
        });

    Event::dispatch(new Lockout(Request::create('https://vendra.test')));

    expect($captured)->toBe(['Authentication lockout triggered.', 'auth_lockout'])
        ->and(RequestJobContext::current()->operation)->toBeNull();
});
