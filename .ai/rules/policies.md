---
paths:
  - 'packages/*/src/Policies/**'
---

# Policies

## Policies compose Authorizes* traits, no base class
A policy is a `final class` with no parent. It composes the `Authorizes*Abilities` traits plus `ResolvesPolicyPermissions` from `Misaf\VendraSupport\Authorization` and implements one method:

    protected static function permissionEnum(): string { return ProductPolicyEnum::class; }

Do not hand-write `view()`, `create()`, `update()` etc. — pull in the trait for that ability group instead. Include `AuthorizesSandboxMode` where sandbox restrictions apply.
