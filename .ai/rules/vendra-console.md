---
paths:
  - 'packages/vendra-console/**'
---

# Vendra Console

## Console is an access model, never an identity
`consoles` is modelled by `Models\Console`: one row per user (`consoles.user_id` is unique), `user()` relation, `active()` and `forUser()` scopes. Only an active console grants panel access. Never authenticate against it — the `console` guard resolves the canonical `User`. Do not query `DB::table('consoles')` in source or tests; tests grant access with `Console::factory()->for($user)->create()`.

## Console grants change only through actions; keep one console user
Create console users through `Actions\CreateConsoleUserAction`, grant an existing user through `Actions\GrantConsoleAccessAction`, reset passwords with `vendra-user`'s `UpdateUserPasswordAction` (no console wrapper), revoke through `Actions\RevokeConsoleUserAction`, which locks the active consoles, deactivates the row (granting again reactivates it) and throws `Exceptions\LastConsoleUserException` instead of deactivating the last active one. The CLI surface is `vendra-console:user` (`--username`, `--email`, `--password`, `--revoke`), where `--revoke` identifies the user by `--email` or `--username` and rejects a run that passes both. The first console user is seeded with a generated password printed once — there is no credentials config or env.
