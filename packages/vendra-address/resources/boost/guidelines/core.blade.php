## Vendra Address

### Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

### Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.

- Keep address ownership in `misaf/vendra-address`; never add address tables or provider imports to `vendra-user-profile`.
- Depend one-way on `vendra-user-profile`, register the `addresses` relation dynamically, and register the Filament relation manager through `UserProfileRelationManagers`.
- Keep `AddressPolicy`, `AddressPolicyEnum`, and `PermissionPolicySeeder` aligned so Filament strict authorization remains valid.
- Keep addresses country-adaptable: use ISO country code, locality, administrative area, sorting code, three address lines, locale, and JSON metadata for jurisdiction-specific fields.
- Address metadata is structured provider/country data, not translated content.
- Each user profile has exactly one default address (`is_default`), kept by the synchronous `Observers\AddressObserver`: the first address becomes the default, flagging another clears the rest, the only default cannot be unflagged, and deleting it hands the flag to the oldest remaining address.
