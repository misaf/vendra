---
paths:
  - '**/*.php'
---

# General

## Write short, Laravel-style comments
Comment only where the *why* is non-obvious; never restate a method name, a class name, or the code. `walletFor(Model $user, string $currencyCode): Wallet` needs no "Get the user's wallet for the given currency." — delete such docblocks rather than rewording them. Framework hooks such as a seeder's `run()`, a job's `handle()`, or an action's `execute()` need no summary either; keep only what the caller cannot see, like a lock or a validation the caller owns. The same goes for class docblocks: `SwitchSettingsTask` needs none; keep a class docblock only for the reason behind a non-obvious design. When a comment is warranted, write it in Laravel's voice: an imperative or plain summary sentence ("Attribute a new user to the affiliate who referred them."), then at most a short second paragraph for the reason. Full sentences ending in a period, no em-dash asides, no "Note:" prefixes. Inline `//` comments are one short sentence. Type-only tags (`@var`, `@param` generics, array shapes, `@property`, `@use`, bare `@throws`) stay as-is.
