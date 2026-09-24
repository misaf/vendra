---
paths:
  - 'packages/*/database/migrations/**'
---

# Migrations

## is_default vs is_primary designation flags
Name a "one of many" designation flag with the `is_` prefix, never bare `default`/`primary`: both are reserved words in MySQL, and bare names would clash with `primary()`/`default()` scopes. Plain state booleans stay bare (`active`, `in_stock`).
- `is_default`: the fallback the system picks when nothing is chosen (currency, language, transaction gateway, plan, wishlist, user profile, address). Use the IsDefault* support components and MaintainsSingleActiveDefault.
- `is_primary`: the canonical one among an owner's own identifiers, where the others remain valid and in use (store domain, phone number). Use the IsPrimary* support components.
Enforce one per owner in both cases.
