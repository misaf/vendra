---
paths:
  - 'packages/*/tests/Feature/**'
---

# Tests Feature

## Switch both tenant contexts in Filament tests
When a booted tenant-aware Filament panel test creates records for another tenant, call `Filament::setTenant($otherTenant)` and `switchToTestTenant($otherTenant)`, then restore both contexts. Switching only the support-layer resolver is insufficient because Filament's tenant creation observer associates new resource records with `Filament::getTenant()`.
