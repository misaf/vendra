---
paths:
  - 'packages/*/src/Filament/**'
---

# Filament

## Extract Filament schemas, tables and actions from the Resource
Resources stay thin. Forms, infolists and tables live in sibling classes with a static `configure()` — `Schemas/<Model>Form.php`, `Schemas/<Model>Infolist.php`, `Tables/<Model>Table.php` — and the resource just delegates:

    public static function table(Table $table): Table { return ProductTable::configure($table); }

Custom actions go in `Actions/`, pages in `Pages/`, relation managers in `RelationManagers/`.
Never hardcode a user-facing string: every label, heading and navigation item uses `__('vendra-<package>::attributes.key')` (or `::navigation.`), with the string added to the package's `lang/` files.

## Filament actions stay resource-scoped and thin
Filament actions live under src/Filament/.../<Resource>/Actions/ as one independent class per operation with Table/Bulk/Page suffix (e.g. SuspendStoreTableAction), never plural *Actions aggregators. They own only label/visibility/schema/notification and delegate writes to a domain action in src/Actions. No DB writes, dispatches, or state transitions inline. Shared helpers go in Actions/Concerns/ within the same resource. Permission attach/detach/sync plumbing is the only exempt thin wrapper.

## Status fields use badges, booleans use icons
Status/state enum values (`status`, `*_status`, `desired_state`) render as `TextEntry`/`TextColumn` `->badge()`. Boolean values render as `IconEntry`/`IconColumn` `->boolean()` (or a toggle column when edited inline) — never as a text badge or a prefix badge next to the name. For `active`, `is_default` and `is_primary` use the shared vendra-support components (`IsActive*`, `IsDefault*`, `IsPrimary*`) instead of hand-building them; add missing variants there.
