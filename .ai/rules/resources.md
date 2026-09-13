---
paths:
  - 'packages/*/src/Filament/Resources/**'
---

# Resources

## Scope a panel's records in getEloquentQuery(), never only in the table
Filament's table, record actions, and global search all build on Resource::getEloquentQuery(). Scoping only the table via modifyQueryUsing() leaves the other two open.

Never write `where('fk', $maybeNullId)`: Eloquent turns a null value into `whereNull`, so a nullable reseller column silently matches every reseller-less row. In the reseller panel that meant an offboarded reseller (OffboardResellerAction soft-deletes the Reseller but leaves its user able to sign in) saw every platform-owned store. Guard the null case explicitly and return no rows.
