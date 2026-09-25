## Vendra Inquiry

The `misaf/vendra-inquiry` package owns storefront contact enquiries — what a customer wrote in, and whether a person has written back — plus the Filament inbox for them.

### Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

### Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.
- Store rules a store administrator may change (the occasion slugs the storefront contact form may send) live in `Settings\InquirySettings` (group `inquiry`, tenant repository), registered with `RegistersSettings` in the service provider and seeded as the platform default by `database/settings`. `Filament\Pages\ManageInquirySettings` edits them in the admin System cluster; read them with `resolve(InquirySettings::class)`, never from config.

- Keep inquiry domain code inside `packages/vendra-inquiry` using the `Misaf\VendraInquiry` namespace.
- `Inquiry` stores the sender's name, email, optional phone, optional occasion slug, the message verbatim, its status, and where it came from.
- Store the message exactly as it was written: it is evidence of what a customer asked for, so never trim, reformat, or interpret it on the way in.
- Keep this a contact inbox, not a ticketing system. There are no threads, assignees, SLAs, or canned replies here; a person answers by email.
- Keep the sender's own words out of `$translatable`: a customer's message and name are user data in one language, stored in scalar columns.
- Treat `occasion` as a stable slug chosen from `InquirySettings::$occasions`, and let the storefront translate it for display.
- Keep enquiries uncreatable from the administration UI: they arrive from the storefront through `SubmitInquiryAction`. The action does not validate; its caller does (the storefront API through `misaf/vendra-inquiry-api`'s `SubmitInquiryRequest`).
- Status is a Spatie model-states machine in `States\` (`Open` stored as `new`, `Answered`, `Closed`), allowing every move between different statuses. Track the reply with `markAnswered()`, `close()`, and `reopen()`; `AnswerInquiryTransition` stamps `answered_at` and `ReopenInquiryTransition` clears it. Filament actions are visible when `canTransitionTo()` allows their target.
- Derive tenant awareness through `misaf/vendra-support`. Apply `BelongsToTenant` to `Inquiry`. Never assign `tenant_id` directly or add a `tenant_aware` config toggle.
- Keep `InquiryResource` in the shared `CustomersCluster` under `src/Filament/Clusters/Resources`, with forms in `Schemas`, tables in `Tables`, and status changes in `Actions`.
- Keep the complete resource tree under `src/Filament/Clusters/Resources/`, use the matching `Misaf\VendraInquiry\Filament\Clusters\Resources` namespace, and keep plugin registration aligned. Any future resource without a `$cluster` must instead live under `src/Filament/Resources/`.
- Keep `InquiryResource` ungrouped and assign `$navigationSort` from `NavigationPriority::Inquiries`; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Badge the navigation with the count of enquiries still waiting for a reply.
- Use `InquiryPolicyEnum` as the permission source, and update `PermissionPolicySeeder` when abilities change.
- Update English, German, and Persian translation files together and keep their keys in parity.
- Add focused Pest coverage for submission validation, the status transitions, migration constraints, policies, and Filament registration.
- Keep architecture expectations enforcing that `Misaf\VendraInquiry` does not use `Misaf\VendraTenant`.
