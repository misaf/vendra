---
paths:
  - '**/database/seeders/**'
---

# Seeders

## Seeders call Actions, never Artisan commands
A seeder must not run a command (`$this->command->call(...)`, `Artisan::call`). Commands are operator tools with prompts and CLI-only output. Inject the domain Action into the seeder's constructor and share derived values (e.g. `Support\ConsoleAddress`) instead of reaching into the command.

## Seeders run through Artisan
`db:seed` is the only supported way to run a seeder, in production and in tests alike: `Artisan::call('db:seed', ['--class' => SomeSeeder::class, '--force' => true])`. Do not call `resolve(Seeder::class)->__invoke()` or `->run()` directly, and do not guard console output behind `isset($this->command)` — `$this->command` is always set. Seeders must still run under `--no-interaction`. Reference: vendra-console `ConsoleSeeder`.
