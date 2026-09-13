---
paths:
  - 'app/Settings/**'
---

# Settings

## Settings rows are keyed by scope, not by tenant_id
`settings.tenant_id` is nullable (null = platform-wide) and `settings.scope` is its non-null projection: `global`, or `tenant:{id}`. The unique key and every upsert conflict target are `(scope, group, name)` — a nullable composite unique would let MySQL insert a second global row on every save, because it counts each NULL as distinct.

Two repositories in `app/Settings/SettingsRepositories/`: `tenant` (default; writes the current store's scope, reads platform rows as defaults so a fresh store never throws MissingSettings) and `global` (platform only). Both drop the tenant global scopes and write through the query builder, because `TenantScope` adds no constraint at all when no tenant is current and `BelongsToTenant::creating` would stamp a platform row with the current tenant.

Never register `settings` in `TenantTableRegistry`: `vendra-tenant:enable` backfills null tenant ids and forces the column NOT NULL. Keep `settings.cache.enabled` false — the cache key is the class and its prefix is static, so it sits above the scoping.
