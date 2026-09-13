---
paths:
  - 'packages/vendra-console/**'
---

# Vendra Console

## ConsoleUser is a grant model, never an identity
`console_users` is modelled by `Models\ConsoleUser` (`user()` relation, `forUser()` scope). Never authenticate against it — the `console` guard resolves the canonical `User`. Do not query `DB::table('console_users')` in source or tests; tests grant access with `ConsoleUser::factory()->for($user)->create()`.

## Console grants change only through actions; keep one console user
Create console users through `Actions\CreateConsoleUserAction`, grant an existing user through `Actions\GrantConsoleAccessAction`, reset passwords with `vendra-user`'s `UpdateUserPasswordAction` (no console wrapper), revoke through `Actions\RevokeConsoleUserAction`, which locks every grant and throws `Exceptions\LastConsoleUserException` instead of removing the last one. The CLI surface is `console:user` (`--email`, `--password`, `--revoke`). The first console user is seeded with a generated password printed once — there is no credentials config or env.
