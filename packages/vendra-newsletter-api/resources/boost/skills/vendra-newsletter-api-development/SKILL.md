---
name: vendra-newsletter-api-development
description: "Create, modify, review, or test packages/vendra-newsletter-api. Use for NewsletterSubscriptionResource, SubscribeNewsletterRequest, SubscribeNewsletterProcessor, public subscription validation and throttling, tenant isolation, and provider wiring."
---

# Vendra Newsletter API

## Workflow

- Inspect this manifest, sibling code, and tests before changes.
- Use Laravel Boost application-info and search-docs for installed API behavior.
- Apply laravel-best-practices and testing-best-practices.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

## Module Boundary

- Keep API DTOs, validation, processors, providers, and HTTP tests in this package under `Misaf\VendraNewsletterApi`.
- Expose only `POST /api/marketing/newsletter-subscriptions`, accepting email and optional name and returning 204 without a body. Keep subscriber IDs, tenant IDs, tokens, and status private; do not add public subscriber reads or deletes.
- Collect denormalization errors so malformed typed input returns 422 rather than 500. Validate email and name, normalize email before calling domain actions, and limit the public operation to 10 requests per minute.
- Delegate writes to Newsletter's `SubscribeNewsletterSubscriberAction` and `ResubscribeNewsletterSubscriberAction` in one transaction. Repeat subscribed requests leave the row unchanged; opted-out and deleted subscribers can opt in again while keeping their token.
- Keep persistence, send jobs, Filament UI, and the token-based unsubscribe endpoint in `misaf/vendra-newsletter`.
- Inherit tenant scoping from the domain models through Support; never import a concrete tenant provider or accept client-selected tenancy.
- API Platform generates the routes from the resource. Register its directory and tag the processor in `NewsletterApiServiceProvider`.
- Run feature and package checks through host `composer test` with TIA, followed by PHPStan, Rector, Pint, and `git diff --check`.
