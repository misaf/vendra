## Vendra Phone

### Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

### Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.

- Keep phone ownership in `misaf/vendra-phone`; never add phone tables or imports to `vendra-user-profile`.
- Register `phoneNumbers` dynamically and contribute UI through `UserProfileRelationManagers`.
- Keep `PhoneNumberPolicy`, `PhoneNumberPolicyEnum`, and `PermissionPolicySeeder` aligned so Filament strict authorization remains valid.
- Use `ysfkaya/filament-phone-input` with ISO country state and canonical E.164 number storage. Do not implement a competing free-text phone input.
- Preserve international flexibility through open phone type strings and metadata.
- Each user profile has exactly one primary phone number (`is_primary`), kept by the synchronous `Observers\PhoneNumberObserver`, which uses vendra-support's `MaintainsSingleFlagPerOwner`; the `primary_profile_guard` unique index backs it in the database.
