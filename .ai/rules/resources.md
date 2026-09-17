---
paths:
  - 'packages/*/src/Filament/Resources/**'
---

# Resources

## Scope a panel's records in getEloquentQuery(), never only in the table
Filament's table, record actions, and global search all build on Resource::getEloquentQuery(). Scoping only the table via modifyQueryUsing() leaves the other two open.

Never write `where('fk', $maybeNullId)`: Eloquent turns a null value into `whereNull`, so a nullable reseller column silently matches every reseller-less row. In the reseller panel an unresolved reseller (`Reseller::forUser()` returns null) would see every platform-owned store. Panel access now requires the main account of an active, non-offboarded reseller, but that gate is not the scope: guard the null case explicitly and return no rows.
