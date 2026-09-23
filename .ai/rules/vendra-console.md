---
paths:
  - 'packages/vendra-console/**'
---

# Vendra Console

## Console is an access model, never an identity
`consoles` is modelled by `Models\Console`: one row per user (`consoles.user_id` is unique), `user()` relation, `active()` and `forUser()` scopes. Only an active console grants panel access. Never authenticate against it — the `console` guard resolves the canonical `User`. Do not query `DB::table('consoles')` in source or tests; tests grant access with `Console::factory()->for($user)->create()`.

## Console grants change only through actions; keep one console user
Create console users through `Actions\CreateConsoleUserAction`, grant an existing user through `Actions\GrantConsoleAccessAction`, reset passwords with `vendra-user`'s `UpdateUserPasswordAction` (no console wrapper), revoke through `Actions\RevokeConsoleUserAction`, which locks the active consoles, deactivates the row (granting again reactivates it) and throws `Exceptions\LastConsoleUserException` instead of deactivating the last active one. The CLI surface is one command per job: `vendra-console:user-create`, `vendra-console:user-password`, `vendra-console:user-grant` and `vendra-console:user-revoke`, sharing `Console\Commands\Concerns\IdentifiesConsoleUser` for `--email`/`--username` normalization only; the lookup is `User::query()->tenantless()->identifiedBy($email, $username)` from `vendra-user`. No command falls through into another's job; one whose user is in the wrong state fails and names the sibling to run (`user-create`, which checks a taken email or username with `UserRules::unique()`, names both `user-grant` and `user-password`). Whenever both identifiers are supplied they must resolve to the same tenantless user: one that names nobody fails naming that identifier, and two that name different users are rejected as mismatched. `user-create` requires `--email` and `--username`, asks for either one an interactive run omits (with Laravel Prompts, asking again until the answer passes `UserRules`), and never falls back to `vendra-console.default_email`, which only the seeder assigns and `user-password` falls back to. `user-grant --password` sets the password only alongside a new grant; for a user who already has access it fails and points at `user-password`. The first console user is seeded with a generated password printed once — there is no credentials config or env.
