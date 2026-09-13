---
paths:
  - 'packages/*/src/Actions/**'
---

# Actions

## Action classes expose a single execute()
An Action is `final class <Verb><Noun>Action` with one public `execute(...)` method — never `handle()` or `__invoke()`. Dependencies come through constructor promotion.

Input validation and normalization (trimming, lowercasing) belong to the caller — the Filament form, console command, or API layer, which acts as the controller — and run before `execute()`; the action assumes validated, normalized input. Canonical storage formats a model enforces itself (`User`'s `email` attribute lowercases and trims) stay on the model. Actions still enforce invariants that need their lock or transaction (the last administrator, one user per reseller, the last console user) by throwing, and database unique indexes stay the final guard.
Add `Spatie\QueueableAction\QueueableAction` when the action must also run on the queue.

## One verb per action; reads do not get an action
An action that grew a second public verb is not an action — split it (`StartStoreStorefrontAction`, `StopStoreStorefrontAction`, `RestartStoreStorefrontAction`), or rename it and move it out of `Actions/`.

A method that records nothing and decides nothing is a read, not an operation: let the caller reach the port or the model directly rather than adding a pass-through wrapper. `storefront:lifecycle` does this — start/stop/restart go through actions because they record desired state, while status and logs call `StorefrontProvisioner` straight.

Wrap the whole operation in `DB::transaction()` whenever it performs more than one write, and take a row lock when it reads a value it is about to write back. A demote-then-create left half-applied is how a store ends up with no active domain.

## Domain actions stay in src/Actions
Domain actions live only here as final <Verb><Noun>Action with single execute(). No Filament imports, no notifications, no table/page types. Callers validate input before execute(); actions enforce locked invariants by throwing. Multi-write ops wrap in DB::transaction with row locks.
