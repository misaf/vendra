---
paths:
  - 'packages/vendra-*/src/Filament/Resources/Stores/**'
---

# Stores

## Keep Store Edit descriptive, not operational
Store Edit pages expose only legitimate descriptive fields such as name and description. Keep the billing reseller, tenant slug, active domain, activation/suspension, provisioning identifiers/status, and storefront runtime state out of direct forms; change them through the existing authorized domain actions. Reseller Store record resolution must remain scoped in StoreResource::getEloquentQuery().
