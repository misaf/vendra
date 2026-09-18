## Vendra Inquiry API

The `misaf/vendra-inquiry-api` package owns API Platform resources (`ApiResource` DTOs), state processors, and service-provider wiring for `misaf/vendra-inquiry`.

### Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

### Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Keep inquiry API code inside `packages/vendra-inquiry-api` using the `Misaf\VendraInquiryApi` namespace.
- Keep the inquiry model, migration, factory, policy, seeder, status transitions, and Filament inbox in `misaf/vendra-inquiry`; this package only validates and routes.
- Expose exactly one operation: `POST /support/inquiries`.
- Keep the operation unauthenticated but throttled — anyone may write to a shop — and answer `204` with `output: false`.
- Never expose a read operation for enquiries. What customers wrote in is inbox material for the studio, not a public collection.
- Capture the source and the sender's locale from the request rather than the body, so neither can be spoofed.
- Mirror `SubmitInquiryAction`'s rules in `SubmitInquiryRequest` so the storefront gets the same answer the action would give, and delegate the write itself to that action.
- Enforce the `vendra-inquiry.occasions` allow-list in `SubmitInquiryProcessor`, where the HTTP operation and the MCP tool meet: `SubmitInquiryRequest::RULES` is a constant and cannot read configuration.
- Give input DTOs with multi-word properties an explicit `#[SerializedName]`: the configured name converter otherwise maps camelCase wire names onto snake_case PHP properties and silently drops them.
- Rely on the inquiry model's support-layer tenant scope and keep production API code free of `Misaf\VendraTenant`. Feature tests may use a concrete tenant factory solely to establish tenant context.
- Keep Pest architecture tests and focused resource/processor tests current.
