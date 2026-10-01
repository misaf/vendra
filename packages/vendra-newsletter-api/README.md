# Vendra Newsletter API

Public newsletter subscriptions through API Platform. Requires PHP 8.4+, Laravel 13, `misaf/vendra-api`, `misaf/vendra-newsletter`, and `misaf/vendra-support`.

## Installation

```bash
composer require misaf/vendra-newsletter-api
```

The service provider is auto-discovered. Publish and run Newsletter's migrations when installing the domain package for the first time; this API package has no migrations.

## Subscription

```http
POST /api/marketing/newsletter-subscriptions
Content-Type: application/json

{"email":"reader@example.com","name":"Reader"}
```

Returns `204 No Content` for successful new, repeat, or restored subscriptions. Email is required and normalized to lowercase; name is optional. Both fields have a 255-character limit. Invalid input returns 422, and requests above 10 per minute return 429.

Writes belong to Newsletter's domain actions. A repeat subscription preserves the existing name and token. An opted-out subscriber opts in again; a deleted subscriber is restored and may receive the submitted name. The response exposes no subscriber identifiers, tokens, or subscription status. No read, update, or delete API is exposed.

The host resolves the current tenant through the shared API middleware; the client cannot supply a tenant ID. Domain model scoping keeps identical emails independent across tenants. The API layer uses no concrete tenant provider.

Use Newsletter's existing token-based web unsubscribe link to opt out. Newsletter management, sending, and scheduling remain in the domain package's admin UI.

## Verification

Run from the monorepo host:

```bash
composer test -- --testsuite=vendra-newsletter-api,vendra-newsletter
composer stan
vendor/bin/rector process packages/vendra-newsletter-api
vendor/bin/pint --dirty --format agent
git diff --check
```
